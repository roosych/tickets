<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Связь тикета с записью во внешней системе (например, задание ServiceRadar).
     * Пара external_system + external_id уникальна: повторный запрос на создание вернёт уже созданный тикет.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('external_system', 50)->nullable()->after('due_date');
            $table->string('external_id', 100)->nullable()->after('external_system');
            $table->unique(['external_system', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropUnique(['external_system', 'external_id']);
            $table->dropColumn(['external_system', 'external_id']);
        });
    }
};
