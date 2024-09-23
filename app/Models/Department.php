<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Department extends Model
{
    protected $fillable = [
        'name',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }


    public function manager(): HasOne
    {
        return $this->hasOne(User::class);
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
