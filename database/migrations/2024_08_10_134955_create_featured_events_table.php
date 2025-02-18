<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('featured_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignUlid('event_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->integer('order')->default(0);
            $table->timestamp('active_from')->nullable();
            $table->timestamp('active_until')->nullable();
            $table->timestamps();

            // Ensure one event can only be featured once
            $table->unique('event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('featured_events');
    }
};
