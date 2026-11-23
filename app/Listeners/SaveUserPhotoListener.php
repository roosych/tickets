<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use LdapRecord\Laravel\Events\Import\Saved;
use Throwable;

class SaveUserPhotoListener
{
    // Аватары выводятся максимум ~100px — больше хранить незачем
    private const AVATAR_SIZE = 400;

    // Потолок памяти для декодирования фото. Фото крупнее этого пропускаем,
    // чтобы вход пользователя не падал из-за огромного снимка в AD
    private const MAX_MEMORY_BYTES = 768 * 1024 * 1024;

    public function handle(Saved $event): void
    {
        $ldapUser = $event->object;
        $photo = $ldapUser->getFirstAttribute('jpegphoto');
        $fileName = $ldapUser->getFirstAttribute('samaccountname');

        if (! $photo || ! $fileName) {
            return;
        }

        // Фото — необязательная часть входа: любая ошибка здесь не должна ломать логин
        try {
            $info = @getimagesizefromstring($photo);
            if (! $info) {
                return;
            }

            [$width, $height] = $info;

            // GD держит несжатое изображение: ~5 байт на пиксель при чтении + столько же при обработке.
            // Фото 6400×4791 из AD требует ~340 МБ при лимите 128 МБ — PHP падал с фатальной ошибкой (500 при входе)
            $needed = (int) ($width * $height * 10) + memory_get_usage(true) + 32 * 1024 * 1024;
            if ($needed > self::MAX_MEMORY_BYTES) {
                Log::warning("AD photo of {$fileName} is too large ({$width}x{$height}), skipped");
                return;
            }
            $this->ensureMemory($needed);

            $image = Image::read($photo);
            unset($photo);

            // Квадрат по центру по горизонтали, от верхнего края (лицо обычно сверху), сразу уменьшенный
            $size = min($width, $height, self::AVATAR_SIZE);
            $image->cover($size, $size, 'top');

            Storage::disk('public')->put('images/users/' . $fileName . '.jpg', (string) $image->toJpeg(85));
        } catch (Throwable $e) {
            Log::warning("Failed to save AD photo of {$fileName}: " . $e->getMessage());
        }
    }

    private function ensureMemory(int $bytes): void
    {
        $limit = ini_get('memory_limit');
        if ($limit === '-1') {
            return;
        }

        $current = $this->toBytes($limit);
        if ($current < $bytes) {
            ini_set('memory_limit', (string) $bytes);
        }
    }

    private function toBytes(string $value): int
    {
        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => (int) $value,
        };
    }
}
