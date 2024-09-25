<?php

use App\Models\Department;
use App\Models\Priorities;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id()->from(101);
            $table->text('text')->nullable();
            $table->text('voice_message')->nullable();
            $table->foreignIdFor(User::class, 'user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignIdFor(Department::class)
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignIdFor(Priorities::class)
                ->constrained()
                ->cascadeOnUpdate();
            $table->foreignIdFor(User::class, 'executor_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignIdFor(Ticket::class, 'parent_id')
                ->nullable()
                ->constrained('tickets')
                ->cascadeOnDelete();
            $table->string('status');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
