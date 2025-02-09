<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->event = Event::factory()->create();
});

test('can list event participants', function (): void {
    EventParticipant::factory()->count(3)->create([
        'event_id' => $this->event->id,
    ]);

    $response = $this->getJson('/api/events/' . $this->event->slug . '/participants');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'event_id',
                    'user_id',
                    'status',
                    'registration_date',
                    'check_in_date',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
});

test('can create an event participant', function (): void {
    $payload = [
        'user_id' => $this->user->id,
        'status' => 'registered',
        'registration_date' => now()->toDateTimeString(),
    ];

    $response = $this->postJson('/api/events/' . $this->event->slug . '/participants', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'event_id' => $this->event->id,
                'user_id' => $this->user->id,
                'status' => 'registered',
            ],
        ]);

    $this->assertDatabaseHas('event_participants', [
        'event_id' => $this->event->id,
        'user_id' => $this->user->id,
        'status' => 'registered',
    ]);
});

test('can show an event participant', function (): void {
    $participant = EventParticipant::factory()->create([
        'event_id' => $this->event->id,
    ]);

    $response = $this->getJson('/api/events/' . $this->event->slug . '/participants/' . $participant->id);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $participant->id,
                'event_id' => $this->event->id,
                'user_id' => $participant->user_id,
                'status' => $participant->status,
            ],
        ]);
});

test('can update an event participant', function (): void {
    $participant = EventParticipant::factory()->create([
        'event_id' => $this->event->id,
    ]);

    $payload = [
        'status' => 'checked_in',
        'check_in_date' => now()->toDateTimeString(),
    ];

    $response = $this->putJson('/api/events/' . $this->event->slug . '/participants/' . $participant->id, $payload);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $participant->id,
                'status' => 'checked_in',
            ],
        ]);

    $this->assertDatabaseHas('event_participants', [
        'id' => $participant->id,
        'status' => 'checked_in',
    ]);
});

test('can delete an event participant', function (): void {
    $participant = EventParticipant::factory()->create([
        'event_id' => $this->event->id,
    ]);

    $response = $this->deleteJson('/api/events/' . $this->event->slug . '/participants/' . $participant->id);

    $response->assertStatus(204);
    $this->assertDatabaseMissing('event_participants', ['id' => $participant->id]);
});

test('validates required fields when creating event participant', function (): void {
    $response = $this->postJson('/api/events/' . $this->event->slug . '/participants', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'user_id',
            'status',
            'registration_date',
        ]);
});

test('validates unique user participation in event', function (): void {
    $existingParticipant = EventParticipant::factory()->create([
        'event_id' => $this->event->id,
        'user_id' => $this->user->id,
    ]);

    $payload = [
        'user_id' => $this->user->id,
        'status' => 'registered',
        'registration_date' => now()->toDateTimeString(),
    ];

    $response = $this->postJson('/api/events/' . $this->event->slug . '/participants', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['user_id']);
});

test('validates valid status when creating event participant', function (): void {
    $payload = [
        'user_id' => $this->user->id,
        'status' => 'invalid_status',
        'registration_date' => now()->toDateTimeString(),
    ];

    $response = $this->postJson('/api/events/' . $this->event->slug . '/participants', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});
