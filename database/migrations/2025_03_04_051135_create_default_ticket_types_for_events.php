<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Migrations\Migration;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all events
        $events = Event::all();

        foreach ($events as $event) {
            // Check if the event already has ticket types
            $hasTicketTypes = TicketType::where('event_id', $event->id)->exists();

            // If not, create default ticket types
            if ( ! $hasTicketTypes) {
                // Create a free general admission ticket type
                TicketType::create([
                    'event_id' => $event->id,
                    'created_by' => $event->user_id,
                    'name' => 'General Admission',
                    'price' => 0,
                    'quantity' => 0, // 0 for unlimited
                    'description' => 'Standard entry to the event',
                    'status' => 'active',
                ]);

                // Create a premium ticket type
                TicketType::create([
                    'event_id' => $event->id,
                    'created_by' => $event->user_id,
                    'name' => 'Premium',
                    'price' => 25.00,
                    'quantity' => 50,
                    'description' => 'Premium access with additional benefits',
                    'status' => 'active',
                ]);

                // Create a VIP ticket type
                TicketType::create([
                    'event_id' => $event->id,
                    'created_by' => $event->user_id,
                    'name' => 'VIP',
                    'price' => 50.00,
                    'quantity' => 20,
                    'description' => 'VIP access with exclusive perks',
                    'status' => 'active',
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration as it's just adding data
    }
};
