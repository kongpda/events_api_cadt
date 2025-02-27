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
        Schema::table('event_participants', function (Blueprint $table): void {
            // Drop the foreign key constraint first
            $table->dropForeign(['ticket_id']);

            // Rename the column and change the type from ULID (string) to normal ID (integer)
            $table->renameColumn('ticket_id', 'ticket_type_id');
            $table->unsignedBigInteger('ticket_type_id')->change();

            // Add the new foreign key constraint
            $table->foreign('ticket_type_id')
                ->references('id')
                ->on('ticket_types')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_participants', function (Blueprint $table): void {
            // Drop the new foreign key constraint
            $table->dropForeign(['ticket_type_id']);

            // Rename back to the original column name and change the type back to ULID (string)
            $table->renameColumn('ticket_type_id', 'ticket_id');
            $table->string('ticket_id', 26)->change();

            // Add back the original foreign key constraint
            $table->foreign('ticket_id')
                ->references('id')
                ->on('tickets')
                ->onDelete('set null');
        });
    }
};
