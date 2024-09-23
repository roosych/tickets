<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Language extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id', 'name',
        'active', 'default', 'fallback',
    ];

    protected $casts = [
        'active' => 'boolean',
        'default' => 'boolean',
        'fallback' => 'boolean',
    ];

    //вызывается когда модель уже загружена
    public static function booted(): void
    {
        //подписка на сохранение модели каждый раз когда будет изменена чтобы сбрасывать кеш
        static::saved(function (self $model) {
            Cache::forget('languages');
        });

        static::deleted(function (self $model) {
            Cache::forget('languages');
        });
    }

    public static function findCurrent(): Model
    {
        return self::getActive()
            ->firstWhere('id', app()->getLocale());
    }

    public static function findActive(string $id): Model
    {
        return self::getActive()
            ->firstWhere('id', $id);
    }

    public static function findDefault(): Model|null
    {
        return self::getActive()
            ->firstWhere('default', true);
    }

    public static function findFallback(): Model|null
    {
        return self::getActive()
            ->firstWhere('fallback', true);
    }

    public static function getActive(): Collection
    {
//        return self::query()
//            ->where('active', true)
//            ->get();
        return Cache::remember(
            key: 'languages',
            ttl: now()->addDay(),
            callback: function () {
                return self::query()
                    ->where('active', true)
                    ->get();
            }
        );
    }


    /*
     * метод в момент запроса расчитвает какой префикс добавить (use in RouteServiceProvider)
     */
    public static function routePrefix(): string|null
    {
        $prefix = request()->segment(1);

        $activeLanguages = static::getActive();

        //если префикса запроса нет в активных языках
        if ($activeLanguages->doesntContain('id', $prefix)) {
            $prefix = null;
        }
        return $prefix;
    }
}
