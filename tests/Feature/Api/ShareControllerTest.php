<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Share;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->event = Event::factory()->create();
});

test('can list shares', function (): void {
    Share::factory()->count(3)->create([
        'event_id' => $this->event->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->getJson('/api/events/' . $this->event->slug . '/shares');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'event_id',
                    'user_id',
                    'platform',
                    'share_url',
                    'shared_at',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
});

test('can create a share', function (): void {
    $payload = [
        'user_id' => $this->user->id,
        'platform' => 'facebook',
        'share_url' => 'https://facebook.com/share/123',
        'shared_at' => now()->toDateTimeString(),
    ];

    $response = $this->postJson('/api/events/' . $this->event->slug . '/shares', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'event_id' => $this->event->id,
                'user_id' => $this->user->id,
                'platform' => 'facebook',
                'share_url' => 'https://facebook.com/share/123',
            ],
        ]);

    $this->assertDatabaseHas('shares', [
        'event_id' => $this->event->id,
        'user_id' => $this->user->id,
        'platform' => 'facebook',
    ]);
});

test('can show a share', function (): void {
    $share = Share::factory()->create([
        'event_id' => $this->event->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->getJson('/api/events/' . $this->event->slug . '/shares/' . $share->id);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $share->id,
                'event_id' => $this->event->id,
                'user_id' => $this->user->id,
                'platform' => $share->platform,
            ],
        ]);
});

test('can update a share', function (): void {
    $share = Share::factory()->create([
        'event_id' => $this->event->id,
        'user_id' => $this->user->id,
    ]);

    $payload = [
        'platform' => 'twitter',
        'share_url' => 'https://twitter.com/share/456',
    ];

    $response = $this->putJson('/api/events/' . $this->event->slug . '/shares/' . $share->id, $payload);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $share->id,
                'platform' => 'twitter',
                'share_url' => 'https://twitter.com/share/456',
            ],
        ]);

    $this->assertDatabaseHas('shares', [
        'id' => $share->id,
        'platform' => 'twitter',
        'share_url' => 'https://twitter.com/share/456',
    ]);
});

test('can delete a share', function (): void {
    $share = Share::factory()->create([
        'event_id' => $this->event->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->deleteJson('/api/events/' . $this->event->slug . '/shares/' . $share->id);

    $response->assertStatus(204);
    $this->assertDatabaseMissing('shares', ['id' => $share->id]);
});

test('validates required fields when creating share', function (): void {
    $response = $this->postJson('/api/events/' . $this->event->slug . '/shares', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'user_id',
            'platform',
            'share_url',
            'shared_at',
        ]);
});

test('validates valid platform when creating share', function (): void {
    $payload = [
        'user_id' => $this->user->id,
        'platform' => 'invalid_platform',
        'share_url' => 'https://example.com/share',
        'shared_at' => now()->toDateTimeString(),
    ];

    $response = $this->postJson('/api/events/' . $this->event->slug . '/shares', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['platform']);
});
