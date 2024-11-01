<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\TicketStatusEnum;
use App\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;

class User extends Authenticatable implements LdapAuthenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AuthenticatesWithLdap, HasPermissions;

    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager', 'distinguishedname');
    }

    public function getDepartmentId()
    {
        // Возвращаем department_id пользователя или department_id его руководителя
        return $this->department_id ?: optional($this->head)->department_id;
    }

    public function getDepartment(): ?Department
    {
        return Department::query()
            //->with(['roles', 'tags']) // Жадная загрузка ролей
            ->where('id', $this->getDepartmentId())
            ->first();
    }

    public function isManager(): bool
    {
        return $this->is_manager === 'manager';
    }

//    public function deptUsers(): array|Collection
//    {
//        return self::where('manager', auth()->user()->manager)->get();
//    }

    public function deptUsers(): array|Collection
    {
        // Если пользователь — менеджер, то выбираем сотрудников, у которых он является менеджером
        if ($this->isManager()) {
            return self::where('manager', $this->distinguishedname)->get();
        }

        // Если пользователь — не менеджер, то выбираем сотрудников отдела его руководителя
        return self::where('manager', $this->manager)->get();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketsByExecutor(): HasMany
    {
        return $this->hasMany(Ticket::class, 'executor_id');
    }

    public function ticketHistories(): HasMany
    {
        return $this->hasMany(TicketHistory::class)->latest();
    }

//    public function deptAllUsers(): Collection
//    {
//        $deptUsers = $this->deptUsers();
//        $head = $this->head()->get();
//        return $head->merge($deptUsers);
//    }

    public function deptAllUsers(): Collection
    {
        // Получаем сотрудников отдела
        $deptUsers = $this->deptUsers();

        // Если пользователь является менеджером, добавляем его в коллекцию
        if ($this->isManager()) {
            // Добавляем текущего пользователя (менеджера) в коллекцию сотрудников
            $deptUsers->push($this);
        } else {
            // Если пользователь не менеджер, получаем его руководителя и добавляем к сотрудникам
            $head = $this->head()->get();
            $deptUsers = $deptUsers->merge($head);
        }

        return $deptUsers;
    }

    public function getAvatarAttribute(): string|null
    {
        //return Storage::url('images/users/'.$this->username.'.jpg');
        $avatarPath = 'images/users/'.$this->username.'.jpg';

        if (Storage::disk('public')->exists($avatarPath)) {
            return Storage::url($avatarPath);
        }

        return null; // Или путь к изображению по умолчанию
    }

    public function getTicketsCountByStatus(TicketStatusEnum $status): int
    {
        return $this->ticketsByExecutor()
            ->where('status', $status->value)
            ->whereColumn('user_id', '!=', 'executor_id')
            ->count();
    }

    public function getTicketsByStatus(TicketStatusEnum $status): HasMany
    {
        return $this->ticketsByExecutor()->where('status', $status->value);
    }

    public function unreadMentions(): HasMany
    {
        return $this->hasMany(Mention::class)
            ->whereNull('read_at');
    }


    protected $fillable = [
        'name',
        'email',
        'password',
        'department',
        'department_id',
        'last_login',
        'telegram_id',
        'visible',
        'active',
        'tg_notify',
        'email_notify',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
