<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use LdapRecord\Laravel\Events\Import\Saved;

class SaveUserPhotoListener
{
    public function __construct()
    {
        //
    }

    public function handle(Saved $event): void
    {
        $ldapUser = $event->object;
        $photo =  $ldapUser->getFirstAttribute('jpegphoto');
        $fileName  =  $ldapUser->getFirstAttribute('samaccountname');
        if ($photo) {
            $path = 'images/users/' . $fileName . '.jpg';

            $image = Image::read($photo);
            // ширина и высота изображения
            $width = $image->width();
            $height = $image->height();

            // размер квадрата (минимум из ширины и высоты)
            $size = min($width, $height);

            // Координаты для обрезки (центр изображения)
            $x = ($width - $size) / 2;
            $y = 0; //($height - $size) / 2;

            $image->crop($size, $size, $x, $y);
            Storage::disk('public')->put($path, $image->encode());
        }
    }
}
