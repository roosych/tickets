<?php

namespace App\Services;

use App\Enums\FilterGroupingEnum;
use App\Enums\TicketActionEnum;
use App\Enums\TicketStatusEnum;
use App\Events\TicketEvent;
use App\Exceptions\TicketAccessException;
use App\Http\Filters\TicketFilter;
use App\Models\Comment;
use App\Models\Department;
use App\Models\Media;
use App\Models\Tag;
use App\Models\TemporaryFile;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketRating;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;

class TicketService
{
    public function updateTicketStatus(Ticket $ticket, TicketStatusEnum $status, ?string $comment = null): void
    {
        // Пропускаем проверку если тикет уже в нужном статусе
        if ($ticket->status->is($status)) {
            return;
        }

        $this->checkTicketStatus(
            $ticket,
            [TicketStatusEnum::CANCELED, TicketStatusEnum::COMPLETED],
            'Тикет закрыт или отменен!'
        );

        // Если здесь произойдет исключение, транзакция откатится
        DB::transaction(function () use ($ticket, $status, $comment) {
            $ticket->status = $status;

            // Назначаем исполнителя только если статус НЕ отменен и исполнитель еще не назначен
            if ($status !== TicketStatusEnum::CANCELED && $ticket->performer === null) {
                $ticket->executor_id = Auth::id();
            }

            $ticket->save();
            // Перезагружаем отношение
            $ticket->load('performer');

            $ticketHistory = TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'action' => TicketActionEnum::UPDATE_STATUS,
                'status' => $status->value,
                'comment' => $comment,
                'assign_user' => $ticket->performer->id ?? null,
            ]);

            $recipients = $this->getRecipientsForStatusUpdate($ticket);
            event(new TicketEvent($ticket, 'status_updated', $recipients, Auth::user(),
                ['ticket_history_id' => $ticketHistory->id]));
        });
    }

    public function closeTicket(Ticket $ticket, array $ratingData = null): void
    {
        DB::transaction(function () use ($ticket, $ratingData) {
            $this->checkTicketStatus(
                $ticket,
                [TicketStatusEnum::CANCELED, TicketStatusEnum::COMPLETED],
                'Тикет уже закрыт или отменен!'
            );

            // Проверка статуса самого тикета
            if (!$ticket->status->is(TicketStatusEnum::DONE)) {
                abort(403, 'Тикет еще не выполнен');
            }

            if ($ticket->allChildren()->exists()) {
                $ticketChildren = $ticket->allChildren()->get();

                foreach ($ticketChildren as $child) {
                    if (!in_array($child->status, [
                        TicketStatusEnum::DONE,
                        TicketStatusEnum::COMPLETED,
                        TicketStatusEnum::CANCELED
                    ])) {
                        abort(403, 'У тикета есть невыполненные подтикеты');
                    }
                }

                // Закрываем все подтикеты в статусе DONE
                foreach ($ticketChildren as $child) {
                    if ($child->status->is(TicketStatusEnum::DONE)) {
                        $this->updateTicketStatus($child, TicketStatusEnum::COMPLETED);
                    }
                }
            }

            // Если переданы данные рейтинга и для тикета требуется рейтинг
            if ($ratingData && $ticket->requiresRating()) {
                // Создаем запись рейтинга
                TicketRating::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => auth()->id(),
                    'rating' => $ratingData['rating'],
                    'comment' => $ratingData['comment'],
                ]);
            }

            $this->updateTicketStatus($ticket, TicketStatusEnum::COMPLETED);
        });
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

            foreach ($ticketChildren as $child) {
                if (!in_array($child->status, [
                    TicketStatusEnum::COMPLETED,
                    TicketStatusEnum::CANCELED
                ])) {
                    abort(403, 'У тикета есть невыполненные подтикеты');
                }
            }
        }

        $this->updateTicketStatus($ticket, TicketStatusEnum::DONE, $comment);
    }

    public function cancelTicket(Ticket $ticket, string $comment): void
    {
        $this->checkTicketStatus(
            $ticket,
            [TicketStatusEnum::CANCELED, TicketStatusEnum::COMPLETED],
            'Тикет уже закрыт или отменен!'
        );

        if ($ticket->allChildren()->exists()) {
            $ticketChildren = $ticket->allChildren()->get();

            // Отменяем только те подтикеты, которые не в статусе COMPLETED и не в статусе CANCELED
            foreach ($ticketChildren as $child) {
                if (!$child->status->is(TicketStatusEnum::COMPLETED) && !$child->status->is(TicketStatusEnum::CANCELED)) {
                    $this->updateTicketStatus($child, TicketStatusEnum::CANCELED);
                }
            }
        }

        $this->updateTicketStatus($ticket, TicketStatusEnum::CANCELED, $comment);
    }

    public function addComment(Ticket $ticket, array $data, string $folder): Comment
    {
        $this->checkTicketStatus(
            $ticket,
            [TicketStatusEnum::CANCELED, TicketStatusEnum::COMPLETED],
            'Нельзя комментировать закрытый или отмененный тикет!'
        );

        DB::beginTransaction();
        try {
            // Создаем комментарий
            $comment = Comment::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'text' => $data['text'],
            ]);

            // Обрабатываем упоминания
            if (!empty($data['mentions'])) {
                $mentions = collect($data['mentions'])
                    ->unique()
                    ->map(function ($userId) use ($comment) {
                        return [
                            'user_id' => $userId,
                            'comment_id' => $comment->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    })
                    ->all();

                $comment->mentions()->insert($mentions);
            }

            // Перемещение временных файлов
            $tempPath = storage_path('app/public/uploads/tmp/' . $folder);

            if (File::exists($tempPath)) {
                $files = File::files($tempPath);

                foreach ($files as $file) {
                    $filename = $file->getFilename();
                    $uniqueFilename = Str::uuid();
                    $extension = $file->getExtension();
                    $size = $file->getSize();

                    $destinationPath = 'uploads/tickets/' . $ticket->id . '/comments/' . $uniqueFilename . '.' . $extension;

                    // Копируем файл
                    Storage::disk('public')->put($destinationPath, File::get($file));

                    // Сохраняем в таблицу media
                    Media::create([
                        'mediable_type' => Comment::class,
                        'mediable_id' => $comment->id,
                        'folder' => 'comments/' . $comment->id,
                        'filename' => $filename,
                        'unique_filename' => $uniqueFilename,
                        'size' => $size,
                        'extension' => $extension,
                    ]);
                }

                // Удаляем временную папку
                Storage::disk('public')->deleteDirectory('uploads/tmp/' . $folder);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        // Получатели уведомлений
        $recipients = $this->getRecipientsForComment($ticket, $comment);

        // Вызов события
        event(new TicketEvent($ticket, 'commented', $recipients, Auth::user(), [
            'comment_id' => $comment->id
        ]));

        return $comment->load('mentions');
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

        // Выполняем обновление данных в рамках транзакции
        DB::transaction(function () use ($ticket, $user) {
            // Обновляем исполнителя и статус тикета
            $ticket->update([
                'executor_id' => $user->id,
                'status' => TicketStatusEnum::OPENED,
            ]);

            // Создаем запись в истории тикета
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'action' => TicketActionEnum::ASSIGN_USER,
                'status' => TicketStatusEnum::OPENED,
                'assign_user' => $user->id,
            ]);
        });

        // Отправляем событие после успешного назначения исполнителя
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

    public function createTicket(array $data, string $folder): Ticket
    {
        DB::beginTransaction();
        try {
            // Создание тикета
            $isPrivate = auth()->user()->isManager() ? ($data['is_private'] ?? false) : false;
            $department = Department::find($data['department']);

            $executorId = $isPrivate
                ? ($department->manager->id ?? null)
                : ($data['user'] ?? null);

            $ticket = Ticket::query()->create([
                'text' => $data['text'],
                'user_id' => $data['client'] ?? auth()->id(),
                'priorities_id' => $data['priority'],
                'department_id' => $data['department'],
                'status' => TicketStatusEnum::OPENED,
                'executor_id' => $executorId,
                'parent_id' => $data['parent_id'] ?? null,
                'is_private' => $isPrivate,
            ]);

            // Синхронизация тегов
            $ticket->tags()->sync($data['tags'] ?? []);

            // Перемещение временных файлов и создание записей в таблице Media
            $tempPath = storage_path('app/public/uploads/tmp/' . $folder);

            if (File::exists($tempPath)) {
                $files = File::files($tempPath);

                foreach ($files as $file) {
                    $filename = $file->getFilename();
                    $unique_filename = Str::uuid();
                    $extension = $file->getExtension();
                    $size = $file->getSize();

                    // Куда копируем
                    $destinationPath = 'uploads/tickets/' . $ticket->id . '/' . $unique_filename . '.' . $extension;

                    // Копируем файл
                    Storage::disk('public')->put($destinationPath, File::get($file));

                    // Записываем в media
                    Media::create([
                        'mediable_type' => Ticket::class,
                        'mediable_id' => $ticket->id,
                        'folder' => 'tickets/' . $ticket->id,
                        'filename' => $filename,
                        'unique_filename' => $unique_filename,
                        'size' => $size,
                        'extension' => $extension,
                    ]);
                }

                // Удаляем временную папку
                Storage::disk('public')->deleteDirectory('uploads/tmp/' . $folder);
            }

            // Фиксируем транзакцию
            DB::commit();

            // Отправка события о создании тикета
            $recipients = $this->getRecipientsForCreation($ticket);
            event(new TicketEvent($ticket, 'created', $recipients, Auth::user()));

            return $ticket;

        } catch (\Exception $e) {
            // Откат транзакции в случае ошибки
            DB::rollBack();
            throw $e;
        }
    }

    // получатели уведомлений
    private function getRecipientsForStatusUpdate(Ticket $ticket): array
    {
        $recipients = collect();
        $recipients->push($ticket->creator);

        if ($ticket->performer) {
            $recipients->push($ticket->performer);
        }

        // Если у тикета есть родитель, добавляем создателя родительского тикета
        if ($ticket->parent) {
            $recipients->push($ticket->parent->creator);
        }

        // Фильтруем получателей по условиям
        $recipients = $recipients->filter(function ($user) use ($ticket) {
            return $user
                && $user->id !== Auth::id() //todo сделать без фасада Auth
                && $user->email_notify == true;
        });

        // Удаляем дубликаты
        return $recipients->unique('id')->pluck('id')->toArray();
    }

    private function getRecipientsForComment(Ticket $ticket, Comment $comment): array
    {
        $recipients = collect();
        $recipients->push($ticket->creator);

        if ($ticket->performer) {
            $recipients->push($ticket->performer);
        }

        // Фильтруем получателей по условиям
        $recipients = $recipients->filter(function ($user) use ($comment) {
            return $user
                && $user->id !== $comment->creator->id // исключаем создателя комментария
                && $user->email_notify == true;
        });

        // Удаляем дубликаты
        return $recipients->unique('id')->pluck('id')->toArray();
    }

    private function getRecipientsForCreation(Ticket $ticket): array
    {
        $recipients = collect();

        // Проверяем, назначен ли перформер
        if ($ticket->performer) {
            // Если перформер назначен, добавляем его к получателям
            $recipients->push($ticket->performer);
        } else {
            // Если перформер не назначен, получаем всех сотрудников департамента
            $departmentUsers = $recipients->merge(
                User::where('department_id', $ticket->department_id)
                    ->get()
            );

            $recipients = $recipients->merge($departmentUsers);

            // Добавляем менеджера департамента, если он существует
            if ($ticket->department->manager) {
                $recipients->push($ticket->department->manager);
            }
        }
        // Фильтруем получателей по условиям
        $recipients = $recipients->filter(function ($user) use ($ticket) {
            return $user
                && $user->id !== $ticket->creator->id
                && $user->email_notify == true;
        });

        // Удаляем дубликаты
        return $recipients->unique('id')->pluck('id')->toArray();
    }

    private function getRecipientsForAssign(Ticket $ticket): array
    {
        $recipients = collect();

        if ($ticket->performer && $ticket->performer->email_notify) {
            $recipients->push($ticket->performer);
        }

        return $recipients->unique('id')->pluck('id')->toArray();
    }

    // вспомогательные проверки
    public function isDepartmentHead(Ticket $ticket): bool
    {
        // Проверяем, если текущий пользователь начальник департамента и департамент тикета совпадает
        return auth()->user()->isManager() && auth()->user()->department_id === $ticket->department_id;
    }

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

    //
    public function getFilteredAndGroupedTickets(array $data)
    {
        $tickets = QueryBuilder::for(Ticket::class)
            ->allowedFilters(TicketFilter::filter())
            ->when(isset($data['date_range']), function ($query) use ($data) {
                return $query->filterByDateRange($data['date_range']);
            })
            ->with(['tags', 'performer', 'priority'])
            ->where('tickets.department_id', auth()->user()->getDepartmentId())
            ->whereColumn('user_id', '!=', 'executor_id')
            ->whereNotNull('executor_id')
            ->visibleToUser()
            ->get();

        // Вызываем метод группировки и возвращаем результаты
        return $this->groupTickets($tickets, request('grouping', FilterGroupingEnum::USER->value));
    }

    private function groupTickets($tickets, ?string $grouping)
    {
        return match (FilterGroupingEnum::tryFrom($grouping)) {
            FilterGroupingEnum::TAG => $tickets
                ->flatMap(function ($ticket) {
                    return $ticket->tags->map(function ($tag) use ($ticket) {
                        return ['tag' => $tag, 'ticket' => $ticket];
                    });
                })
                ->groupBy('tag.id')
                ->map(function ($groupedTickets) {
                    // Вернем тег и связанные тикеты для каждого id тега
                    $tag = $groupedTickets->first()['tag'];
                    $tickets = $groupedTickets->pluck('ticket');

                    return [
                        'tag' => $tag,
                        'tickets' => $tickets,
                    ];
                }),
            FilterGroupingEnum::PRIORITY => $tickets->groupBy('priorities_id'),
            default => $tickets->groupBy('executor_id'), // Группировка по исполнителю по умолчанию
        };
    }
}
