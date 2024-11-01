<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('visible')->default(true);
            $table->boolean('active')->default(true);
            $table->boolean('tg_notify')->default(true);
            $table->boolean('email_notify')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('visible');
            $table->dropColumn('active');
            $table->dropColumn('tg_notify');
            $table->dropColumn('email_notify');
        });
    }
};
