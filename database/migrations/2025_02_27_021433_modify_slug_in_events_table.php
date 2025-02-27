<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            // Drop the unique constraint
            $table->dropUnique(['slug']);

            // Make the slug field nullable
            $table->string('slug')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            // Restore the unique constraint
            $table->unique('slug');

            // Make the slug field required again
            $table->string('slug')->nullable(false)->change();
        });
    }
};
