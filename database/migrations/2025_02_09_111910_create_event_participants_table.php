<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignUlid('event_id')
                ->constrained('events')
                ->onDelete('cascade');
            $table->foreignUlid('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->string('status');
            $table->string('participation_type');
            $table->foreignUlid('ticket_id')
                ->nullable()
                ->constrained('tickets')
                ->onDelete('set null');
            $table->timestamp('check_in_time')->nullable();
            $table->timestamp('joined_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_participants');
    }
};
