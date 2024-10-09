<?php

namespace App\Models;

use App\Enums\TicketActionEnum;
use App\Enums\TicketStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketHistory extends Model
{
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assign_user');
    }

    public function timeBetweenAssignAndProgress(User $user): ?string
    {
        // Получаем последнее время обновления статуса "В процессе"
        $lastInProgress = self::where('ticket_id', $this->ticket_id)
            ->where('action', TicketActionEnum::UPDATE_STATUS)
            ->where('status', TicketStatusEnum::IN_PROGRESS)
            ->where('assign_user', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Если найдено событие "В процессе", продолжаем
        if ($lastInProgress) {
            // Получаем время назначения пользователя
            $assignedRecord = self::where('ticket_id', $this->ticket_id)
                ->where('action', TicketActionEnum::ASSIGN_USER)
                ->where('assign_user', $user->id)
                ->orderBy('created_at', 'desc')
                ->first();
            // Проверяем, найдено ли время назначения
            if ($assignedRecord) {
                $assignedAt = $assignedRecord->created_at;
                // Получаем объект DateInterval
                $interval = $lastInProgress->created_at->diff($assignedAt);
                // Форматируем результат с правильными окончаниями
                $hours = $interval->h;
                $minutes = $interval->i;

                $hoursText = trans_choice('час|часа|часов', $hours, ['count' => $hours]);
                $minutesText = trans_choice('минута|минуты|минут', $minutes, ['count' => $minutes]);
                return trim(($hours ? $hours . ' ' . $hoursText : '') . ' ' . ($minutes ? $minutes . ' ' . $minutesText : ''));
            }
        }

        return null; // Если статус не найден или назначение отсутствует
    }

    public function timeBetweenStatuses(User $user, TicketStatusEnum $fromStatus, TicketStatusEnum $toStatus): ?string
    {
        // Получаем запись о статусе "от" для пользователя
        $fromHistory = self::where('ticket_id', $this->ticket_id)
            ->where('assign_user', $user->id)
            ->where('status', $fromStatus)
            ->orderBy('created_at', 'desc')
            ->first();

        // Получаем запись о статусе "до" для пользователя
        $toHistory = self::where('ticket_id', $this->ticket_id)
            ->where('assign_user', $user->id)
            ->where('status', $toStatus)
            ->orderBy('created_at', 'desc')
            ->first();

        // Если оба статуса найдены, считаем разницу
        if ($fromHistory && $toHistory && $fromHistory->created_at < $toHistory->created_at) {
            // Получаем объект DateInterval
            $interval = $toHistory->created_at->diff($fromHistory->created_at);

            // Форматируем результат с правильными окончаниями
            $hours = $interval->h;
            $minutes = $interval->i;

            $hoursText = trans_choice('час|часа|часов', $hours, ['count' => $hours]);
            $minutesText = trans_choice('минута|минуты|минут', $minutes, ['count' => $minutes]);

            return trim(($hours ? $hours . ' ' . $hoursText : '') . ' ' . ($minutes ? $minutes . ' ' . $minutesText : ''));
        }

        return null; // Если статусы не найдены или неправильно расположены
    }

    protected $fillable = [
        'user_id', 'ticket_id',
        'action', 'status', 'comment',
        'assign_user',
    ];

    protected $casts = [
        'action' => TicketActionEnum::class,
        'status' => TicketStatusEnum::class,
    ];
}
