<?php

declare(strict_types=1);

use App\Models\Ticket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all tickets without tokens
        $tickets = Ticket::whereNull('token')->get();

        foreach ($tickets as $ticket) {
            $ticket->token = Str::random(32);
            $ticket->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration
    }
};
