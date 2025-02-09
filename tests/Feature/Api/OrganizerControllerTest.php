<?php

declare(strict_types=1);

use App\Models\Organizer;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

test('can list organizers', function (): void {
    Organizer::factory()->count(3)->create();

    $response = $this->getJson('/api/organizers');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'email',
                    'phone',
                    'website',
                    'social_links',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
});

test('can create an organizer', function (): void {
    $payload = [
        'name' => 'Test Organizer',
        'slug' => 'test-organizer',
        'description' => 'Test organizer description',
        'email' => 'organizer@test.com',
        'phone' => '+1234567890',
        'website' => 'https://test-organizer.com',
        'social_links' => [
            'facebook' => 'https://facebook.com/test-organizer',
            'twitter' => 'https://twitter.com/test-organizer',
        ],
    ];

    $response = $this->postJson('/api/organizers', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'name' => 'Test Organizer',
                'slug' => 'test-organizer',
                'email' => 'organizer@test.com',
            ],
        ]);

    $this->assertDatabaseHas('organizers', [
        'name' => 'Test Organizer',
        'slug' => 'test-organizer',
        'email' => 'organizer@test.com',
    ]);
});

test('can show an organizer', function (): void {
    $organizer = Organizer::factory()->create();

    $response = $this->getJson('/api/organizers/' . $organizer->slug);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $organizer->id,
                'name' => $organizer->name,
                'slug' => $organizer->slug,
                'email' => $organizer->email,
            ],
        ]);
});

test('can update an organizer', function (): void {
    $organizer = Organizer::factory()->create();

    $payload = [
        'name' => 'Updated Organizer',
        'slug' => 'updated-organizer',
        'description' => 'Updated description',
        'email' => 'updated@test.com',
        'website' => 'https://updated-organizer.com',
    ];

    $response = $this->putJson('/api/organizers/' . $organizer->slug, $payload);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'Updated Organizer',
                'slug' => 'updated-organizer',
                'email' => 'updated@test.com',
            ],
        ]);

    $this->assertDatabaseHas('organizers', [
        'id' => $organizer->id,
        'name' => 'Updated Organizer',
        'email' => 'updated@test.com',
    ]);
});

test('can delete an organizer', function (): void {
    $organizer = Organizer::factory()->create();

    $response = $this->deleteJson('/api/organizers/' . $organizer->slug);

    $response->assertStatus(204);
    $this->assertDatabaseMissing('organizers', ['id' => $organizer->id]);
});

test('validates required fields when creating organizer', function (): void {
    $response = $this->postJson('/api/organizers', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'name',
            'slug',
            'email',
        ]);
});

test('validates email format when creating organizer', function (): void {
    $payload = [
        'name' => 'Test Organizer',
        'slug' => 'test-organizer',
        'email' => 'invalid-email',
    ];

    $response = $this->postJson('/api/organizers', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('validates unique slug when creating organizer', function (): void {
    $existingOrganizer = Organizer::factory()->create();

    $payload = [
        'name' => 'New Organizer',
        'slug' => $existingOrganizer->slug,
        'email' => 'new@test.com',
    ];

    $response = $this->postJson('/api/organizers', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['slug']);
});
