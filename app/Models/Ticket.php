<?php

namespace App\Models;

use App\Enums\TicketActionEnum;
use App\Enums\TicketStatusEnum;
use App\Traits\MediaTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Ticket extends Model
{
    use MediaTrait;

    // Минимальный интервал между записями просмотра в минутах
    public const VIEW_INTERVAL_MINUTES = 15;

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priorities::class, 'priorities_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executor_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Ticket::class, 'parent_id');
    }

    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    public function getUsersByDepartment($departmentId): Collection
    {
        $managers = User::where('department_id', $departmentId)->get();
        $users = User::whereIn('manager', $managers->pluck('distinguishedname'))->get();
        // Объединяем пользователей и менеджеров в одну коллекцию
        return $users->concat($managers);
    }

//    public function user(): BelongsTo
//    {
//        return $this->belongsTo(User::class);
//    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TicketHistory::class);
    }

    public function deadlineChangesCountForUser(User $user): int
    {
        return $this->histories()
            ->where('user_id', $user->id)
            ->where('action', TicketActionEnum::UPDATE_DEADLINE) // если используешь action для записи
            ->count();
    }

    // Метод для получения просмотров
    public function views(): HasMany
    {
        return $this->histories()->where('action', TicketActionEnum::VIEWED);
    }

    public function isDue(): bool
    {
        return $this->due_date && now()->greaterThan($this->due_date);
    }

    public function timeUntilDueFull(): ?string
    {
        if (!$this->due_date) {
            return null; // дедлайна нет
        }

        $now = now();

        if ($now->greaterThanOrEqualTo($this->due_date)) {
            return 'Дедлайн наступил';
        }

        $diff = $now->diff($this->due_date);

        $days = $diff->d;
        $hours = $diff->h;

        $parts = [];

        if ($days > 0) {
            $parts[] = $days . ' ' . trans_choice('tickets.time_units.day', $days, ['count' => $days]);
        }

        if ($hours > 0) {
            $parts[] = $hours . ' ' . trans_choice('tickets.time_units.hour', $hours, ['count' => $hours]);
        }

        if (empty($parts)) {
            $parts[] = '< 1 час';
        }

        return implode(' ', $parts);
    }


    public function dueProgress(): int
    {
        if (!$this->due_date) {
            return 0;
        }

        $start = $this->created_at;
        $end = $this->due_date;
        $now = now();

        // Если дедлайн прошёл
        if ($now->greaterThanOrEqualTo($end)) {
            return 100;
        }

        // Если тикет ещё не стартовал (маловероятно)
        if ($now->lessThan($start)) {
            return 0;
        }

        // Общее время интервала
        $totalSeconds = $end->timestamp - $start->timestamp;

        // Прошедшее время с момента создания
        $passedSeconds = $now->timestamp - $start->timestamp;

        // Процент прошедшего времени
        $progress = ($passedSeconds / $totalSeconds) * 100;

        return max(0, min(100, (int) round($progress)));
    }



    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'ticket_tag');
    }

    public function getDoneTicketComment()
    {
        // Получаем последний комментарий со статусом "done"
        return $this->histories
            ->where('status', TicketStatusEnum::DONE)
            ->sortByDesc('created_at')
            ->first()
            ?->comment;
    }

    public function getCanceledTicketComment()
    {
        // Получаем последний комментарий со статусом "canceled"
        return $this->histories
            ->where('status', TicketStatusEnum::CANCELED)
            ->sortByDesc('created_at')
            ->first()
            ?->comment;
    }

    public function scopeFilterByDateRange(Builder $query, string $dateRange): Builder
    {
        $dates = explode(' - ', $dateRange);
        if (count($dates) === 2) {
            return $query->whereBetween('tickets.created_at', [
                Carbon::parse($dates[0])->startOfDay(),
                Carbon::parse($dates[1])->endOfDay()
            ]);
        }
        return $query;
    }

    // Метод для получения времени последнего изменения статуса для конкретного пользователя
//    public function getLastStatusChangeTime(TicketStatusEnum $status, int $userId)
//    {
//        // Получаем последнюю запись статуса для указанного assign_user
//        $lastHistory = $this->histories()
//            ->where('status', $status)
//            ->where('assign_user', $userId)
//            ->orderBy('created_at', 'desc')
//            ->first();
//
//        return $lastHistory?->created_at;
//    }

    public function getLastStatusChangeTimes(int $userId): Collection
    {
        // Получаем все записи статусов для указанного assign_user
        return $this->histories()
            ->where('assign_user', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('status'); // Группируем по статусу
    }

    public function isPrivate(): bool
    {
        return $this->is_private;
    }

    public function isHidden(): bool
    {
        return $this->is_hidden;
    }

    // Локальные Scopes
    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }

    // Видимы только юзерам (кто не менеджер)
    public function scopeVisibleToUser($query)
    {
        if (!auth()->user()->isManager()) {
            $query->where('is_private', false);
        }
        return $query;
    }

    /**
     * Проверяет, требуется ли рейтинг при закрытии тикета.
     * Рейтинг не требуется, если:
     * - пользователь является и создателем, и исполнителем
     * - исполнитель не назначен
     *
     * @return bool
     */
    public function requiresRating(): bool
    {
        // Если исполнитель не назначен, рейтинг не требуется
        if (!$this->performer || !$this->performer->id) {
            return false;
        }

        // Если текущий пользователь - создатель и не исполнитель, рейтинг требуется
        return auth()->id() === $this->creator->id && auth()->id() !== $this->performer->id;
    }

    public function rating(): HasOne
    {
        return $this->hasOne(TicketRating::class, 'ticket_id');
    }

    protected $fillable = [
        'user_id',
        'text',
        'voice_message',
        'department_id',
        'parent_id',
        'executor_id',
        'priorities_id',
        'status',
        'due_date',
        'client_id',
        'is_private',
        'is_hidden',
    ];

    protected $casts = [
        'status' => TicketStatusEnum::class,
        'due_date' => 'datetime',
    ];
}
