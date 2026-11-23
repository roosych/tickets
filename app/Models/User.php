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

//    public function head(): BelongsTo
//    {
//        return $this->belongsTo(User::class, 'manager', 'distinguishedname');
//    }

    public function head(): ?User
    {
        return Department::where('id', $this->getDepartmentId())
            ->with('manager') // Жадная загрузка менеджера
            ->first()
            ?->manager;
    }

    public const CURRENT_DEPARTMENT_SESSION_KEY = 'current_department_id';

    /** @var array<int>|null */
    protected ?array $accessibleDepartmentIdsCache = null;

    /**
     * Текущий департамент, в котором работает пользователь.
     * Менеджер нескольких отделов выбирает отдел переключателем в шапке (хранится в сессии);
     * для всех остальных это просто department_id.
     */
    public function getDepartmentId()
    {
        if (auth()->id() === $this->id && app()->bound('session')) {
            $current = session(self::CURRENT_DEPARTMENT_SESSION_KEY);
            if ($current && in_array((int) $current, $this->accessibleDepartmentIds(), true)) {
                return (int) $current;
            }
        }

        return $this->department_id ?? ($this->accessibleDepartmentIds()[0] ?? null);
    }

    /**
     * Отделы, в которых пользователь может работать: свой + те, где он менеджер.
     *
     * @return array<int>
     */
    public function accessibleDepartmentIds(): array
    {
        if ($this->accessibleDepartmentIdsCache === null) {
            $managed = Department::where('manager_id', $this->id)->orderBy('name')->pluck('id')->all();
            $ids = $this->department_id ? array_merge([(int) $this->department_id], $managed) : $managed;
            $this->accessibleDepartmentIdsCache = array_values(array_unique(array_map('intval', $ids)));
        }

        return $this->accessibleDepartmentIdsCache;
    }

    public function accessibleDepartments(): Collection
    {
        $ids = $this->accessibleDepartmentIds();

        return $ids ? Department::whereIn('id', $ids)->orderBy('name')->get() : new Collection();
    }

    public function getDepartment(): ?Department
    {
        $id = $this->getDepartmentId();

        if ($id && (int) $id === (int) $this->department_id) {
            return $this->departmentRelation;
        }

        return $id ? Department::find($id) : null;
    }

    public function departmentRelation(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function isManager(): bool
    {
        // Проверяем, есть ли хотя бы один департамент, где этот пользователь является менеджером
        return Department::where('manager_id', $this->id)->exists();
    }

    public function isManagerOf(Department $department): bool
    {
        return $this->id === $department->manager_id;
    }
//    public function isManager(): bool
//    {
//        return $this->is_manager === 'manager';
//    }

//    public function deptUsers(): array|Collection
//    {
//        return self::where('manager', auth()->user()->manager)->get();
//    }

    public function deptUsers(): array|Collection
    {
        $departmentId = $this->getDepartmentId();

        // Сотрудники отдела + его менеджер, даже если сам он числится в другом отделе
        return self::query()
            ->where('active', true)
            ->where(function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId)
                    ->orWhereIn('id', Department::whereKey($departmentId)->whereNotNull('manager_id')->select('manager_id'));
            })
            ->get();
       /* // Если пользователь — менеджер, то выбираем сотрудников, у которых он является менеджером
        if ($this->isManager()) {
            return self::where('manager', $this->distinguishedname)->where('active', true)->get();
        }

        // Если пользователь — не менеджер, то выбираем сотрудников отдела его руководителя
        return self::where('manager', $this->manager)->where('active', true)->get();*/
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
/*        if ($this->isManager()) {
            // Добавляем текущего пользователя (менеджера) в коллекцию сотрудников
            $deptUsers->push($this);
        } else {
            // Если пользователь не менеджер, получаем его руководителя и добавляем к сотрудникам
            $head = $this->head()->get();
            $deptUsers = $deptUsers->merge($head);
        }*/

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

    /**
     * Что показывать под именем вместо почты: подпись, если она задана, иначе почту.
     */
    public function getContactLineAttribute(): ?string
    {
        return $this->tagline ?: $this->email;
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

    public function scopeExcludeFired($query)
    {
        return $query->where('distinguishedname', 'NOT LIKE', '%Fired_Employees%');
    }

    /**
     * Запросы на одобрение, созданные пользователем
     */
    public function createdApprovalRequests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class, 'creator_id');
    }

    /**
     * Запросы на одобрение, которые нужно одобрить/отклонить пользователю
     */
    public function assignedApprovalRequests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class, 'approver_id');
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
        'is_approver',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
