<?php

use App\Models\Department;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Уникальное имя департамента
            $table->timestamps();
        });

        // Добавление колонки и привязка в таблицу юзера
        Schema::table('users', function (Blueprint $table) {
            $table->foreignIdFor(Department::class)
                ->nullable()
                ->after('department')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
