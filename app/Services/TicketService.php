<?php

namespace App\Services;

use App\Enums\TicketActionEnum;
use App\Enums\TicketStatusEnum;
use App\Exceptions\TicketAccessException;
use App\Models\Comment;
use App\Models\Media;
use App\Models\TemporaryFile;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use App\Notifications\TicketCommentNotification;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use App\Notifications\UserAssignedToTicketNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketService
{
    /**
     * @throws TicketAccessException
     */
    public function updateTicketStatus(Ticket $ticket, TicketStatusEnum $status, ?string $comment = null): void
    {
        $this->checkIfTicketIsClosed($ticket);
        //$this->isInSameDepartment($ticket, auth()->user());
        $this->isInSameDepartmentOrCreator($ticket, auth()->user());

        // Если здесь произойдет исключение, транзакция откатится
        DB::transaction(function () use ($ticket, $status, $comment) {
            $ticket->status = $status;
            $ticket->save();

            $ticketHistory = TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'action' => TicketActionEnum::UPDATE_STATUS,
                'status' => $status->value,
                'comment' => $comment,
            ]);

            // если еще нет юзеров, то только тогда добавляется тот кто нажал на кнопку "в процессе"
            $ticket->performers()->exists() || $ticket->performers()->attach(auth()->id());

            foreach ($ticket->allParticipants() as $user) {
                if ($user->email && $user->id !== auth()->id()) {
                    $user->notify(new TicketStatusUpdatedNotification($ticketHistory));
                }
            }
        });
    }

    /**
     * @throws TicketAccessException
     */
    public function closeTicket(Ticket $ticket): void
    {
        $this->checkIfTicketIsClosed($ticket);
        $this->isUserCanCloseTicket($ticket, auth()->user());
        $this->updateTicketStatus($ticket, TicketStatusEnum::CLOSED);
    }

    /**
     * @throws TicketAccessException
     */
    public function addComment(Ticket $ticket, array $data): Comment
    {
        $this->checkIfTicketIsClosed($ticket);
        $this->isTicketCreatorOrPerformer($ticket, auth()->user());

        $comment = Comment::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'text' => $data['text'],
        ]);

        foreach ($ticket->allParticipants() as $user) {
            if ($user->email && $user->id !== auth()->id()) {
                $user->notify(new TicketCommentNotification($comment));
            }
        }
        return $comment;
    }

    /**
     * @throws TicketAccessException
     */
    public function attachUsers(Ticket $ticket, array $userIds): void
    {
        $this->checkIfTicketIsClosed($ticket);
        $this->isInSameDepartment($ticket, auth()->user());
        // Получаем текущих исполнителей тикета
        $currentPerformerIds = $ticket->performers()->pluck('users.id')->toArray();
        $ticket->performers()->sync($userIds);
        $newPerformerIds = array_diff($userIds, $currentPerformerIds);

        User::whereIn('id', $newPerformerIds)->get()->each(function ($user) use ($ticket) {
            if ($user->email) {
                $user->notify(new UserAssignedToTicketNotification($ticket));
            }
        });
    }

    /**
     * @throws TicketAccessException
     */
    public function attachTags(Ticket $ticket, array $data): void
    {
        $this->checkIfTicketIsClosed($ticket);
        $this->isInSameDepartment($ticket, auth()->user());
        $ticket->tags()->sync($data['tags'] ?? []);
    }

    public function createTicket(array $data): Ticket
    {
        DB::beginTransaction();
        try {
            $ticket = Ticket::query()->create([
                'text' => $data['text'],
                'user_id' => auth()->id(),
                'priorities_id' => $data['priority'],
                'department_id' => $data['department'],
                'status' => TicketStatusEnum::OPENED,
            ]);

            $tempFiles = TemporaryFile::all();

            foreach ($tempFiles as $tempFile) {
                Storage::disk('public')->copy(
                    'uploads/tmp/' . $tempFile->folder . '/' . $tempFile->filename,
                    'uploads/tickets/' . $ticket->id . '/' . $tempFile->filename
                );

                Storage::disk('public')->deleteDirectory('uploads/tmp/' . $tempFile->folder);
                Media::create([
                    'mediable_type' => Ticket::class,
                    'mediable_id' => $ticket->id,
                    'folder' => 'tickets/' . $ticket->id,
                    'filename' => $tempFile->filename,
                    'unique_filename' => $tempFile->unique_filename,
                    'size' => $tempFile->size,
                    'extension' => $tempFile->extension,
                ]);
                $tempFile->delete();
            }
            DB::commit();

            foreach ($ticket->allParticipants() as $user) {
                if ($user->email) {
                    $user->notify(new TicketCreatedNotification($ticket));
                }
            }

            return $ticket;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    // проверки связанные с тикетом
    /**
     * @throws TicketAccessException
     */
    protected function isTicketCreatorOrPerformer(Ticket $ticket, $user): bool
    {
        //dd($ticket->creator->id === $user->id);
        //dd($ticket->performers()->where('users.id', $user->id)->exists());
        if (!($ticket->creator->id === $user->id || $ticket->performers()->where('users.id', $user->id)->exists())) {
            throw new TicketAccessException('У вас нет доступа к этому тикету!');
        }
        return true;
    }

    /**
     * @throws TicketAccessException
     */
    protected function isUserCanCloseTicket(Ticket $ticket, $user): bool
    {
        if (! $this->hasPermissionForAction($user, $ticket, 'create')) {
            throw new TicketAccessException('У вас нет доступа к этому тикету!');
        }

        return true;
    }

    /**
     * @throws TicketAccessException
     */
    protected function isInSameDepartment(Ticket $ticket, $user): bool
    {
        if (! $user->getDepartmentId() === $ticket->department_id) {
            throw new TicketAccessException('У вас нет доступа к этому тикету!');
        }
        return true;
    }

    /**
     * @throws TicketAccessException
     */
    protected function isInSameDepartmentOrCreator(Ticket $ticket, $user): bool
    {
        if (! $this->isInSameDepartment($ticket, $user) || $ticket->creator->id === $user->id) {
            throw new TicketAccessException('У вас нет доступа к этому тикету!');
        }
        return true;
    }

    // вспомогательные проверки
    protected function checkIfTicketIsClosed(Ticket $ticket): void
    {
        abort_if($ticket->id == TicketStatusEnum::CLOSED, 403,
            'Невозможно выполнить действие с закрытым тикетом!');
    }

    protected function hasPermissionForAction($user, $model, string $action): bool
    {
        // Получаем коллекцию разрешений пользователя и фильтруем по модели и действию
        return $user->permissions->filter(function ($permission) use ($model, $action) {
            return $permission->model === get_class($model) && $permission->action === $action;
        })->isNotEmpty(); // Проверяем, не пустая ли коллекция после фильтрации
    }
}
