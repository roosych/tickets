<?php

namespace App\Services;

use App\Enums\TicketActionEnum;
use App\Enums\TicketStatusEnum;
use App\Exceptions\TicketAccessException;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Tag;
use App\Models\TemporaryFile;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use App\Notifications\TicketCommentNotification;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use App\Notifications\UserAssignedToTicketNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketService
{
    /**
     * @throws TicketAccessException
     */
    public function updateTicketStatus(Ticket $ticket, TicketStatusEnum $status, ?string $comment = null): void
    {
        $this->checkTicketStatus(
            $ticket,
            [TicketStatusEnum::CANCELED, TicketStatusEnum::COMPLETED],
            'Тикет закрыт или отменен!'
        );

        // Если здесь произойдет исключение, транзакция откатится
        DB::transaction(function () use ($ticket, $status, $comment) {
            $ticket->status = $status;
            if ($ticket->performer === null) {
                $ticket->executor_id = Auth::id();
            }
            $ticket->save();

            $ticketHistory = TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'action' => TicketActionEnum::UPDATE_STATUS,
                'status' => $status->value,
                'comment' => $comment,
            ]);

            if ($ticket->performer) {
                //$ticket->performer->notify(new TicketStatusUpdatedNotification($ticketHistory));
            }
        });
    }

    /**
     * @throws TicketAccessException
     */
    public function closeTicket(Ticket $ticket): void
    {
        $user = Auth::user();
        if ($user->id !== $ticket->creator->id && $user->getDepartmentId() !== $ticket->department->id) {
            abort(403, 'У вас нет прав на закрытие этого тикета');
        }

        $this->checkTicketStatus(
            $ticket,
            [TicketStatusEnum::CANCELED, TicketStatusEnum::COMPLETED],
            'Тикет уже закрыт или отменен!'
        );


        // Проверка статуса самого тикета
        if (!$ticket->status->is(TicketStatusEnum::DONE)) {
            abort(403, 'Тикет еще не выполнен');
        }

        $ticketChildren = $ticket->allChildren()->get();
        $hasUncompleted = false;

        foreach ($ticketChildren as $child) {
            if (!$child->status->is(TicketStatusEnum::COMPLETED)) {
                $hasUncompleted = true;
                break;
            }
        }

        if ($hasUncompleted) {
            abort(403, 'У тикета есть невыполненные подтикеты');
        }

        $this->updateTicketStatus($ticket, TicketStatusEnum::COMPLETED);
    }

    public function completeTicket(Ticket $ticket, string $comment): void
    {
        $user = Auth::user();
        if ($user->id !== $ticket->performer->id && $user->getDepartmentId() !== $ticket->department->id) {
            abort(403, 'У вас нет прав на закрытие этого тикета');
        }

        $this->checkTicketStatus(
            $ticket,
            [TicketStatusEnum::CANCELED, TicketStatusEnum::COMPLETED],
            'Тикет уже закрыт или отменен!'
        );

        if ($ticket->allChildren()->exists()) {
            $ticketChildren = $ticket->allChildren()->get();
            $hasUncompleted = false;

            foreach ($ticketChildren as $child) {
                if (!$child->status->is(TicketStatusEnum::COMPLETED)) {
                    $hasUncompleted = true;
                    break;
                }
            }

            if ($hasUncompleted) {
                abort(403, 'У тикета есть невыполненные подтикеты');
            }
        }

        $this->updateTicketStatus($ticket, TicketStatusEnum::DONE);

    }

    /**
     * @throws TicketAccessException
     */
    public function cancelTicket(Ticket $ticket, string $comment): void
    {
        $user = Auth::user();
        if ($user->id !== $ticket->performer->id && $user->getDepartmentId() !== $ticket->department->id) {
            abort(403, 'У вас нет прав на закрытие этого тикета');
        }

        $this->checkTicketStatus(
            $ticket,
            [TicketStatusEnum::CANCELED, TicketStatusEnum::COMPLETED],
            'Тикет уже закрыт или отменен!'
        );

        if ($ticket->allChildren()->exists()) {
            $ticketChildren = $ticket->allChildren()->get();

            foreach ($ticketChildren as $child) {
                if (!$child->status->is(TicketStatusEnum::COMPLETED)) {
                    $this->updateTicketStatus($child, TicketStatusEnum::CANCELED);
                }
            }
        }

        $this->updateTicketStatus($ticket, TicketStatusEnum::CANCELED, $comment);
    }

    /**
     * @throws TicketAccessException
     */
    public function addComment(Ticket $ticket, array $data): Comment
    {
        $this->checkTicketStatus(
            $ticket,
            [TicketStatusEnum::CANCELED, TicketStatusEnum::COMPLETED],
            'Нельзя комментировать закрытый или отмененный тикет!'
        );
        $this->checkUserAuthorization($ticket);

        $comment = Comment::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'text' => $data['text'],
        ]);

        if ($ticket->performer) {
            //$ticket->performer->notify(new TicketCommentNotification($comment));
        }
        //$ticket->creator->notify(new TicketCommentNotification($comment));


        return $comment;
    }

    /**
     * @throws TicketAccessException
     */
    public function attachUsers(Ticket $ticket, ?User $user): void
    {
        $this->checkIfInSameDepartment(Auth::user(), $ticket);
        $this->checkTicketStatus(
            $ticket,
            [TicketStatusEnum::CANCELED, TicketStatusEnum::COMPLETED],
            'Тикет закрыт или отменен!'
        );

        $ticket->update(['executor_id' => $user->id]);
        //$user->notify(new UserAssignedToTicketNotification($ticket));
    }

    /**
     * @throws TicketAccessException
     */
    public function attachTags(Ticket $ticket, array $data): void
    {
        $this->checkIfInSameDepartment(Auth::user(), $ticket);
        $this->checkTicketStatus(
            $ticket,
            [TicketStatusEnum::CANCELED, TicketStatusEnum::COMPLETED],
            'Тикет закрыт или отменен!'
        );

        // Получаем новые теги для синхронизации
        $newTags = $data['tags'] ?? [];

        // Проверка наличия подтикетов
        if ($ticket->children()->exists()) {
            // Собираем все теги подтикетов
            $subticketTags = $ticket->children()
                ->with('tags')
                ->get()
                ->pluck('tags')
                ->flatten()
                ->pluck('id')
                ->unique()
                ->toArray();

            // Проверяем, есть ли среди новых тегов те, которые уже присутствуют в подтикетах
            $conflictingTags = array_intersect($newTags, $subticketTags);

            if (!empty($conflictingTags)) {
                // Получаем имена конфликтующих тегов для сообщения об ошибке
                $conflictingTagNames = Tag::whereIn('id', $conflictingTags)->pluck('text')->implode(', ');
                abort(403, 'Теги "<strong>' . $conflictingTagNames . '</strong>" уже существуют в подтикетах');
            }
        }

        // Получаем текущие теги тикета
        $currentTags = $ticket->tags()->pluck('id')->toArray();

        // Находим теги, которые добавляются к тикету
        $addedTags = array_diff($newTags, $currentTags);

        // Синхронизируем теги текущего тикета
        $ticket->tags()->sync($newTags);

        // Проверка, является ли тикет подтикетом
        if ($ticket->parent) {
            // Получаем теги родительского тикета
            $parentTags = $ticket->parent->tags()->pluck('id')->toArray();

            // Находим теги, которые нужно удалить у родителя
            $tagsToRemoveFromParent = array_intersect($addedTags, $parentTags);

            // Удаляем эти теги у родителя
            if (!empty($tagsToRemoveFromParent)) {
                $ticket->parent->tags()->detach($tagsToRemoveFromParent);
            }
        }
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
                'executor_id' => $data['user'],
                'parent_id' => $data['parent_id'],
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

            //todo отправить на почту всех сотрудников депарамента тикета
//            foreach ($ticket->allParticipants() as $user) {
//                if ($user->email) {
//                    $user->notify(new TicketCreatedNotification($ticket));
//                }
//            }

            return $ticket;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // вспомогательные проверки
    protected function checkTicketStatus(Ticket $ticket, array|TicketStatusEnum $statuses, string $errorMessage): void
    {
        $statuses = is_array($statuses) ? $statuses : [$statuses];
        if (in_array($ticket->status, $statuses)) {
            abort(403, $errorMessage);
        }
    }

    /**
     * @throws TicketAccessException
     */
    private function checkUserAuthorization(Ticket $ticket): void
    {
        $user = Auth::user();

        $this->checkIfTicketCreator($user, $ticket);
        $this->checkIfTicketPerformer($user, $ticket);
        $this->checkIfInSameDepartment($user, $ticket);
    }

    private function checkIfTicketCreator($user, Ticket $ticket): void
    {
        if ($user->id !== $ticket->creator->id) {
            throw new TicketAccessException('Вы не являетесь создателем этого тикета!');
        }
    }

    private function checkIfTicketPerformer($user, Ticket $ticket): void
    {
        if (!$ticket->performer || $user->id !== $ticket->performer->id) {
            throw new TicketAccessException('Вы не являетесь исполнителем этого тикета!');
        }
    }

    private function checkIfInSameDepartment($user, Ticket $ticket): void
    {
        if ($user->getDepartmentId() !== $ticket->department_id) {
            throw new TicketAccessException('Вы не принадлежите к департаменту, ответственному за этот тикет!');
        }
    }

    // проверки связанные с тикетом
//    /**
//     * @throws TicketAccessException
//     */
//    protected function isTicketCreatorOrPerformer(Ticket $ticket, $user): bool
//    {
//        if (!($ticket->creator->id === $user->id || $ticket->performer->id === $user->id)) {
//            throw new TicketAccessException('У вас нет доступа к этому тикету!');
//        }
//        return true;
//    }
//
//    /**
//     * @throws TicketAccessException
//     */
//    protected function isTicketsDepartmentManager(Ticket $ticket, $user): bool
//    {
//        if (!$ticket->department->manager->id === $user->id) {
//            throw new TicketAccessException('У вас нет доступа к этому тикету!');
//        }
//        return true;
//    }
//
//    /**
//     * @throws TicketAccessException
//     */
//    protected function isUserCanCloseTicket(Ticket $ticket, $user): bool
//    {
//        if (! $this->hasPermissionForAction($user, $ticket, 'create')) {
//            throw new TicketAccessException('У вас нет доступа к этому тикету!');
//        }
//
//        return true;
//    }
//
//    /**
//     * @throws TicketAccessException
//     */
//    protected function isInSameDepartment(Ticket $ticket, $user): bool
//    {
//        if (! $user->getDepartmentId() === $ticket->department_id) {
//            throw new TicketAccessException('У вас нет доступа к этому тикету!');
//        }
//        return true;
//    }
//
//    /**
//     * @throws TicketAccessException
//     */
//    protected function isInSameDepartmentOrCreator(Ticket $ticket, $user): bool
//    {
//        if (! $this->isInSameDepartment($ticket, $user) || $ticket->creator->id === $user->id) {
//            throw new TicketAccessException('У вас нет доступа к этому тикету!');
//        }
//        return true;
//    }
//

}
