<?php

namespace App\Services;

use App\Enums\TicketActionEnum;
use App\Enums\TicketStatusEnum;
use App\Events\TicketEvent;
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

            $recipients = $this->getRecipientsForStatusUpdate($ticket);
            event(new TicketEvent($ticket, 'status_updated', $recipients, Auth::user(),
                ['ticket_history_id' => $ticketHistory->id]));
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

        $this->updateTicketStatus($ticket, TicketStatusEnum::DONE, $comment);

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

        // Проверяем, является ли текущий пользователь создателем или исполнителем тикета
        if ($ticket->creator->id !== auth()->id() && $ticket->performer->id !== auth()->id()) {
            abort(403, 'Вы не можете оставлять комментарии к этому тикету.');
        }

        $comment = Comment::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'text' => $data['text'],
        ]);

        $recipients = $this->getRecipientsForComment($ticket);
        event(new TicketEvent($ticket, 'commented', $recipients, Auth::user(), ['comment_id' => $comment->id]));

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

        $recipients = $this->getRecipientsForAssign($ticket);
        event(new TicketEvent($ticket, 'assigned', $recipients, Auth::user(), null));
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
                'executor_id' => $data['user'] ?? null,
                'parent_id' => $data['parent_id'] ?? null,
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

            $recipients = $this->getRecipientsForCreation($ticket);
            event(new TicketEvent($ticket, 'created', $recipients, Auth::user()));

            return $ticket;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // получатели уведомлений
    private function getRecipientsForStatusUpdate(Ticket $ticket): array
    {
        $recipients = [$ticket->creator];
        if ($ticket->performer) {
            $recipients[] = $ticket->performer;
        }
        // Удаляем пользователя кто сменил статус из списка получателей
        $recipients = array_filter($recipients, function ($user) {
            return $user->id !== Auth::id();
        });

        return array_unique($recipients, SORT_REGULAR);
    }

    private function getRecipientsForComment(Ticket $ticket): array
    {
        $recipients = [$ticket->creator];
        // Добавляем исполнителя тикета, если он есть
        if ($ticket->performer) {
            $recipients[] = $ticket->performer;
        }
        // Удаляем создателя комментария из списка получателей
        $recipients = array_filter($recipients, function ($user) {
            return $user->id !== Auth::id();
        });
        return array_unique($recipients, SORT_REGULAR);
    }

    private function getRecipientsForCreation(Ticket $ticket): array
    {
        // Получаем всех сотрудников департамента
         $recipients = User::where('manager', $ticket->department->manager->distinguishedname)
             ->get()
             ->all();
        // Добавляем менеджера департамента, если он есть
        if ($ticket->department->manager) {
            $recipients[] = $ticket->department->manager;
        }
        // Удаляем создателя тикета из списка получателей
        $recipients = array_filter($recipients, function ($user) {
            return $user->id !== Auth::id();
        });

        return array_unique($recipients, SORT_REGULAR);
    }

    private function getRecipientsForAssign(Ticket $ticket): array
    {
        if ($ticket->performer) {
            $recipients[] = $ticket->performer;
        }

        return array_unique($recipients, SORT_REGULAR);
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
}
