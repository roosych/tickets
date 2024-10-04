<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Priorities extends Model
{
    public function getNameByLocale(): string
    {
        // Декодируем JSON, если name хранится в виде строки JSON
        $nameData = json_decode($this->name, true, JSON_UNESCAPED_UNICODE);

        // Проверяем, является ли декодированное значение массивом
        if (is_array($nameData)) {
            // Получаем текущую локаль
            $currentLocale = app()->getLocale();

            // Возвращаем значение для текущей локали или значение по умолчанию
            return $nameData[$currentLocale] ?? $nameData['en'];
        }

        // Если name не является массивом, возвращаем его как есть
        return $this->name;
    }

    // Этот метод будет возвращать права из кеша
    public static function getCachedPriorities()
    {
        return Cache::remember('priorities', 60 * 60, function () {
            return self::all();
        });
    }

    //вызывается когда модель уже загружена
    public static function booted(): void
    {
        //подписка на сохранение модели каждый раз когда будет изменена, чтобы сбрасывать кеш
        static::saved(function (self $model) {
            Cache::forget('priorities');
        });

        static::deleted(function (self $model) {
            Cache::forget('priorities');
        });
    }

    protected $casts = [
        'name' => 'array',
    ];
}
