<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Favorite;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->event = Event::factory()->create();
});

test('can list favorites', function (): void {
    Favorite::factory()->count(3)->create([
        'event_id' => $this->event->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->getJson('/api/events/' . $this->event->slug . '/favorites');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'event_id',
                    'user_id',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
});

test('can create a favorite', function (): void {
    $payload = [
        'user_id' => $this->user->id,
    ];

    $response = $this->postJson('/api/events/' . $this->event->slug . '/favorites', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'event_id' => $this->event->id,
                'user_id' => $this->user->id,
            ],
        ]);

    $this->assertDatabaseHas('favorites', [
        'event_id' => $this->event->id,
        'user_id' => $this->user->id,
    ]);
});

test('can show a favorite', function (): void {
    $favorite = Favorite::factory()->create([
        'event_id' => $this->event->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->getJson('/api/events/' . $this->event->slug . '/favorites/' . $favorite->id);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $favorite->id,
                'event_id' => $this->event->id,
                'user_id' => $this->user->id,
            ],
        ]);
});

test('can delete a favorite', function (): void {
    $favorite = Favorite::factory()->create([
        'event_id' => $this->event->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->deleteJson('/api/events/' . $this->event->slug . '/favorites/' . $favorite->id);

    $response->assertStatus(204);
    $this->assertDatabaseMissing('favorites', ['id' => $favorite->id]);
});

test('validates required fields when creating favorite', function (): void {
    $response = $this->postJson('/api/events/' . $this->event->slug . '/favorites', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'user_id',
        ]);
});

test('validates unique user favorite for event', function (): void {
    $existingFavorite = Favorite::factory()->create([
        'event_id' => $this->event->id,
        'user_id' => $this->user->id,
    ]);

    $payload = [
        'user_id' => $this->user->id,
    ];

    $response = $this->postJson('/api/events/' . $this->event->slug . '/favorites', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['user_id']);
});
