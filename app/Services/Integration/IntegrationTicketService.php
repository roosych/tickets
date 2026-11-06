<?php

namespace App\Services\Integration;

use App\Enums\TicketActionEnum;
use App\Enums\TicketStatusEnum;
use App\Events\TicketEvent;
use App\Jobs\SendTicketNotification;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Тикеты от внешних систем: создание с ключом идемпотентности, смена дедлайна, исполнителя и статуса от имени технического пользователя.
 */
class IntegrationTicketService
{
    /** Уведомления при создании: всем как обычно, только исполнителю (без Telegram-группы) или никому. */
    public const NOTIFY_ALL = 'all';

    public const NOTIFY_EXECUTOR = 'executor';

    public const NOTIFY_NONE = 'none';

    public function __construct(private TicketService $tickets) {}

    /**
     * Создаёт тикет или возвращает уже созданный с той же парой external_system + external_id.
     *
     * @param  array{external_system: string, external_id: string, text: string, department_id: int, priority_id: int, due_date?: ?string, parent_id?: ?int, parent_external_id?: ?string, executor?: ?array, notify?: string}  $data
     * @return array{0: Ticket, 1: bool} тикет и признак «создан сейчас»
     */
    public function create(array $data, User $robot): array
    {
        if ($existing = $this->findExternal($data['external_system'], $data['external_id'])) {
            return [$existing, false];
        }

        $parentId = $data['parent_id'] ?? null;

        if ($parentId === null && ! empty($data['parent_external_id'])) {
            $parentId = Ticket::query()
                ->where('external_system', $data['external_system'])
                ->where('external_id', $data['parent_external_id'])
                ->where('user_id', $robot->id)
                ->value('id');
        }

        try {
            $ticket = $this->insert($data, $robot, $parentId);
        } catch (UniqueConstraintViolationException) {
            // Тот же тикет параллельно создал другой запрос внешней системы.
            return [$this->findExternal($data['external_system'], $data['external_id']), false];
        }

        $this->notifyCreated($ticket->load(['performer', 'department.manager', 'creator']), $robot, $data['notify'] ?? self::NOTIFY_ALL);

        return [$ticket, true];
    }

    private function findExternal(string $system, string $externalId): ?Ticket
    {
        return Ticket::query()->where('external_system', $system)->where('external_id', $externalId)->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function insert(array $data, User $robot, ?int $parentId): Ticket
    {
        return DB::transaction(function () use ($data, $robot, $parentId): Ticket {
            $ticket = Ticket::query()->create([
                'text' => $data['text'],
                'user_id' => $robot->id,
                'priorities_id' => $data['priority_id'],
                'department_id' => $data['department_id'],
                'status' => TicketStatusEnum::OPENED,
                'executor_id' => $this->findUser($data['executor'] ?? null)?->id,
                'parent_id' => $parentId,
                'due_date' => $this->parseDate($data['due_date'] ?? null),
                'external_system' => $data['external_system'],
                'external_id' => $data['external_id'],
            ]);

            return $ticket;
        });
    }

    /**
     * Дата с часовым поясом из внешней системы — в пояс приложения: в базе даты хранятся без пояса.
     */
    private function parseDate(?string $value): ?Carbon
    {
        return $value === null ? null : Carbon::parse($value)->setTimezone(config('app.timezone'));
    }

    /**
     * Меняет дедлайн и/или исполнителя. Запись в истории — как при ручной смене дедлайна.
     *
     * @param  array{due_date?: ?string, executor?: ?array, comment?: ?string}  $data
     */
    public function update(Ticket $ticket, array $data, User $robot): Ticket
    {
        DB::transaction(function () use ($ticket, $data, $robot): void {
            if (array_key_exists('due_date', $data)) {
                $dueDate = $this->parseDate($data['due_date']);

                if ($ticket->due_date?->toDateTimeString() !== $dueDate?->toDateTimeString()) {
                    $ticket->update(['due_date' => $dueDate]);
                    $ticket->histories()->create([
                        'user_id' => $robot->id,
                        'action' => TicketActionEnum::UPDATE_DEADLINE,
                        'status' => $ticket->status->value,
                        'comment' => $data['comment'] ?? null,
                    ]);
                }
            }

            if (array_key_exists('executor', $data)) {
                $executor = $this->findUser($data['executor']);

                if ($executor !== null && $executor->id !== $ticket->executor_id) {
                    $ticket->update(['executor_id' => $executor->id]);
                    $ticket->histories()->create([
                        'user_id' => $robot->id,
                        'action' => TicketActionEnum::ASSIGN_USER,
                        'status' => $ticket->status->value,
                        'assign_user' => $executor->id,
                    ]);
                    event(new TicketEvent($ticket->load('performer'), 'assigned', [$executor->id], $robot));
                }
            }
        });

        return $ticket->refresh();
    }

    /**
     * Переводит тикет в done или canceled. Закрытые и отменённые тикеты не трогает.
     * done: исполнителем становится тот, кто выполнил работу во внешней системе (если исполнителя ещё нет).
     *
     * @param  array{status: string, comment?: ?string, performed_by?: ?array}  $data
     */
    public function changeStatus(Ticket $ticket, array $data): Ticket
    {
        if (in_array($ticket->status, [TicketStatusEnum::COMPLETED, TicketStatusEnum::CANCELED], true)) {
            return $ticket;
        }

        $status = TicketStatusEnum::from($data['status']);

        if ($status === TicketStatusEnum::CANCELED) {
            $this->tickets->cancelTicket($ticket, $data['comment'] ?? '');

            return $ticket->refresh();
        }

        if ($ticket->executor_id === null && ($performer = $this->findUser($data['performed_by'] ?? null))) {
            $ticket->update(['executor_id' => $performer->id]);
        }

        $this->tickets->updateTicketStatus($ticket, $status, $data['comment'] ?? null);

        return $ticket->refresh();
    }

    /**
     * Пользователь по данным каталога AD: guid, затем логин, затем почта. Неактивных не назначаем.
     *
     * @param  array{guid?: ?string, username?: ?string, email?: ?string}|null  $identity
     */
    public function findUser(?array $identity): ?User
    {
        if (empty($identity)) {
            return null;
        }

        foreach (['guid' => 'guid', 'username' => 'username', 'email' => 'email'] as $key => $column) {
            if (! empty($identity[$key])) {
                $user = User::query()->where($column, $identity[$key])->where('active', true)->first();

                if ($user) {
                    return $user;
                }
            }
        }

        return null;
    }

    private function notifyCreated(Ticket $ticket, User $robot, string $notify): void
    {
        if ($notify === self::NOTIFY_ALL) {
            event(new TicketEvent($ticket, 'created', $this->tickets->getRecipientsForCreation($ticket), $robot));

            return;
        }

        // Подтикеты: только письмо/уведомление исполнителю, без сообщения в Telegram-группу отдела.
        if ($notify === self::NOTIFY_EXECUTOR && $ticket->performer && $ticket->performer->email_notify) {
            SendTicketNotification::dispatch($ticket->performer, $ticket, 'created');
        }
    }
}
