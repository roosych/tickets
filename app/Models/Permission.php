<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Translatable\HasTranslations;

class Permission extends Model
{
    use HasTranslations;

    public array $translatable = ['name', 'group'];

    protected $fillable = [
        'name', 'group', 'action', 'model',
    ];

    // Этот метод будет возвращать права из кеша
    public static function getCachedPermissions()
    {
        return self::all();

        //todo включить кеш перед деплоем
//        return Cache::remember('permissions', 60 * 60, function () {
//            return self::all();
//        });
    }

    //вызывается когда модель уже загружена
    public static function booted(): void
    {
        //подписка на сохранение модели каждый раз когда будет изменена, чтобы сбрасывать кеш
        static::saved(function (self $model) {
            Cache::forget('permissions');
        });

        static::deleted(function (self $model) {
            Cache::forget('permissions');
        });
    }
}
