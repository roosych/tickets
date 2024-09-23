<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temporary_files', function (Blueprint $table) {
            $table->id();
            $table->string('folder');
            $table->string('filename');
            $table->string('unique_filename');
            $table->unsignedBigInteger('size');
            $table->string('extension', 4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temporary_files');
    }
};
