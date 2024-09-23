<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->morphs('mediable'); // Для связи с любой моделью
            $table->string('folder'); // Папка, в которой хранится файл
            $table->string('filename'); // Оригинальное имя файла
            $table->string('unique_filename'); // Уникальное имя файла
            $table->string('size'); // Размер файла
            $table->string('extension', 4); // Расширение файла
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
