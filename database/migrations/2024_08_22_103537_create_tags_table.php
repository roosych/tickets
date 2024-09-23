<?php

use App\Models\Department;
use App\Models\Tag;
use App\Models\Ticket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('text');
            $table->string('color');
            $table->foreignIdFor(Department::class)
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamps();
        });


    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
