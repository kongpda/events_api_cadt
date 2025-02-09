<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->event = Event::factory()->create();
    $this->ticketType = TicketType::factory()->create([
        'event_id' => $this->event->id,
    ]);
});

test('can list tickets', function (): void {
    Ticket::factory()->count(3)->create([
        'event_id' => $this->event->id,
        'ticket_type_id' => $this->ticketType->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->getJson('/api/events/' . $this->event->slug . '/tickets');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'event_id',
                    'ticket_type_id',
                    'user_id',
                    'order_id',
                    'status',
                    'ticket_number',
                    'price',
                    'purchase_date',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
});

test('can create a ticket', function (): void {
    $payload = [
        'ticket_type_id' => $this->ticketType->id,
        'user_id' => $this->user->id,
        'status' => 'reserved',
        'price' => 100.00,
        'purchase_date' => now()->toDateTimeString(),
    ];

    $response = $this->postJson('/api/events/' . $this->event->slug . '/tickets', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'event_id' => $this->event->id,
                'ticket_type_id' => $this->ticketType->id,
                'user_id' => $this->user->id,
                'status' => 'reserved',
                'price' => 100.00,
            ],
        ]);

    $this->assertDatabaseHas('tickets', [
        'event_id' => $this->event->id,
        'ticket_type_id' => $this->ticketType->id,
        'user_id' => $this->user->id,
        'status' => 'reserved',
    ]);
});

test('can show a ticket', function (): void {
    $ticket = Ticket::factory()->create([
        'event_id' => $this->event->id,
        'ticket_type_id' => $this->ticketType->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->getJson('/api/events/' . $this->event->slug . '/tickets/' . $ticket->id);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $ticket->id,
                'event_id' => $this->event->id,
                'ticket_type_id' => $this->ticketType->id,
                'user_id' => $this->user->id,
                'status' => $ticket->status,
            ],
        ]);
});

test('can update a ticket', function (): void {
    $ticket = Ticket::factory()->create([
        'event_id' => $this->event->id,
        'ticket_type_id' => $this->ticketType->id,
        'user_id' => $this->user->id,
    ]);

    $payload = [
        'status' => 'confirmed',
        'ticket_number' => 'TICKET-123',
    ];

    $response = $this->putJson('/api/events/' . $this->event->slug . '/tickets/' . $ticket->id, $payload);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $ticket->id,
                'status' => 'confirmed',
                'ticket_number' => 'TICKET-123',
            ],
        ]);

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => 'confirmed',
        'ticket_number' => 'TICKET-123',
    ]);
});

test('can delete a ticket', function (): void {
    $ticket = Ticket::factory()->create([
        'event_id' => $this->event->id,
        'ticket_type_id' => $this->ticketType->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->deleteJson('/api/events/' . $this->event->slug . '/tickets/' . $ticket->id);

    $response->assertStatus(204);
    $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
});

test('validates required fields when creating ticket', function (): void {
    $response = $this->postJson('/api/events/' . $this->event->slug . '/tickets', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'ticket_type_id',
            'user_id',
            'status',
            'price',
            'purchase_date',
        ]);
});

test('validates ticket type belongs to event', function (): void {
    $otherEvent = Event::factory()->create();
    $otherTicketType = TicketType::factory()->create([
        'event_id' => $otherEvent->id,
    ]);

    $payload = [
        'ticket_type_id' => $otherTicketType->id,
        'user_id' => $this->user->id,
        'status' => 'reserved',
        'price' => 100.00,
        'purchase_date' => now()->toDateTimeString(),
    ];

    $response = $this->postJson('/api/events/' . $this->event->slug . '/tickets', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['ticket_type_id']);
});

test('validates valid status when creating ticket', function (): void {
    $payload = [
        'ticket_type_id' => $this->ticketType->id,
        'user_id' => $this->user->id,
        'status' => 'invalid_status',
        'price' => 100.00,
        'purchase_date' => now()->toDateTimeString(),
    ];

    $response = $this->postJson('/api/events/' . $this->event->slug . '/tickets', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});
