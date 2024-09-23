<?php

namespace App\Traits;

use App\Models\Media;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait MediaTrait
{
    /**
     * Полиморфная связь с медиафайлами
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Получение всех медиафайлов
     */
    public function getMediaFiles(): Collection
    {
        return $this->media; // Вернет коллекцию файлов, связанных с моделью
    }

    /**
     * Получение медиафайлов по расширению
     */
    public function getMediaFilesByExtension($extension): Collection
    {
        return $this->media->where('extension', $extension);
    }
}
