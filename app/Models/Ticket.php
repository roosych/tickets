<?php

namespace App\Models;

use App\Enums\TicketStatusEnum;
use App\Traits\MediaTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ticket extends Model
{
    use MediaTrait;

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priorities::class, 'priorities_id');
    }

    public function performers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->inRandomOrder();
    }

    public function allParticipants(): Collection
    {
        return $this->performers->push($this->creator)->unique('id');
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

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'ticket_tag');
    }

    public function getCompletedTicketComment()
    {
        // Получаем последний комментарий со статусом "completed"
        return $this->histories
            ->where('status', TicketStatusEnum::COMPLETED)
            ->sortByDesc('created_at')
            ->first()
            ?->comment;
    }

    protected $fillable = [
        'user_id',
        'text',
        'voice_message',
        'department_id',
        'priorities_id',
        'status',
    ];

    protected $casts = [
        'status' => TicketStatusEnum::class,
    ];
}
