<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Department extends Model
{
    protected $fillable = [
        'name',
        'active',
        'manager_id',
        'tg_chat_id',
        'tg_topic_id',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class)->where('active', true);
    }


/*    public function manager(): HasOne
    {
        return $this->hasOne(User::class);
    }*/

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class)->latest();
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class)->latest();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
