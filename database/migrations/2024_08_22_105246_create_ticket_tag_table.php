<?php

use App\Models\Tag;
use App\Models\Ticket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('ticket_tag', function (Blueprint $table) {
            $table->foreignIdFor(Ticket::class)
                ->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Tag::class)
                ->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_tag');
    }
};
