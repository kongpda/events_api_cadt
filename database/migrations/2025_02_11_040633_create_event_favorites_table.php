<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('event_favorites', function (Blueprint $table): void {
            $table->id();
            $table->foreignUlid('event_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'event_id']);
        });

        // Drop the old favorites table if it exists
        Schema::dropIfExists('favorites');
    }

    public function down(): void
    {
        Schema::dropIfExists('event_favorites');
    }
};
