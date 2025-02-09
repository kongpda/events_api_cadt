<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->event = Event::factory()->create();
});

test('can list ticket types', function (): void {
    TicketType::factory()->count(3)->create([
        'event_id' => $this->event->id,
    ]);

    $response = $this->getJson('/api/events/' . $this->event->slug . '/ticket-types');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'quantity',
                    'min_per_order',
                    'max_per_order',
                    'sale_start_date',
                    'sale_end_date',
                    'event_id',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
});

test('can create a ticket type', function (): void {
    $payload = [
        'name' => 'VIP Ticket',
        'description' => 'VIP access with special perks',
        'price' => 100.00,
        'quantity' => 50,
        'min_per_order' => 1,
        'max_per_order' => 5,
        'sale_start_date' => now()->addDay()->toDateTimeString(),
        'sale_end_date' => now()->addDays(30)->toDateTimeString(),
    ];

    $response = $this->postJson('/api/events/' . $this->event->slug . '/ticket-types', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'name' => 'VIP Ticket',
                'description' => 'VIP access with special perks',
                'price' => 100.00,
                'quantity' => 50,
            ],
        ]);

    $this->assertDatabaseHas('ticket_types', [
        'name' => 'VIP Ticket',
        'event_id' => $this->event->id,
    ]);
});

test('can show a ticket type', function (): void {
    $ticketType = TicketType::factory()->create([
        'event_id' => $this->event->id,
    ]);

    $response = $this->getJson('/api/events/' . $this->event->slug . '/ticket-types/' . $ticketType->id);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $ticketType->id,
                'name' => $ticketType->name,
                'price' => $ticketType->price,
            ],
        ]);
});

test('can update a ticket type', function (): void {
    $ticketType = TicketType::factory()->create([
        'event_id' => $this->event->id,
    ]);

    $payload = [
        'name' => 'Updated Ticket',
        'description' => 'Updated description',
        'price' => 150.00,
        'quantity' => 75,
    ];

    $response = $this->putJson('/api/events/' . $this->event->slug . '/ticket-types/' . $ticketType->id, $payload);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'Updated Ticket',
                'description' => 'Updated description',
                'price' => 150.00,
                'quantity' => 75,
            ],
        ]);

    $this->assertDatabaseHas('ticket_types', [
        'id' => $ticketType->id,
        'name' => 'Updated Ticket',
        'price' => 150.00,
    ]);
});

test('can delete a ticket type', function (): void {
    $ticketType = TicketType::factory()->create([
        'event_id' => $this->event->id,
    ]);

    $response = $this->deleteJson('/api/events/' . $this->event->slug . '/ticket-types/' . $ticketType->id);

    $response->assertStatus(204);
    $this->assertDatabaseMissing('ticket_types', ['id' => $ticketType->id]);
});

test('validates required fields when creating ticket type', function (): void {
    $response = $this->postJson('/api/events/' . $this->event->slug . '/ticket-types', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'name',
            'price',
            'quantity',
            'min_per_order',
            'max_per_order',
            'sale_start_date',
            'sale_end_date',
        ]);
});

test('validates sale dates when creating ticket type', function (): void {
    $payload = [
        'name' => 'Test Ticket',
        'price' => 100,
        'quantity' => 50,
        'min_per_order' => 1,
        'max_per_order' => 5,
        'sale_start_date' => now()->addDays(5)->toDateTimeString(),
        'sale_end_date' => now()->addDays(2)->toDateTimeString(), // End date before start date
    ];

    $response = $this->postJson('/api/events/' . $this->event->slug . '/ticket-types', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['sale_end_date']);
});
