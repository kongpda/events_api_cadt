<?php

declare(strict_types=1);

use App\Models\Tag;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

test('can list tags', function (): void {
    Tag::factory()->count(3)->create();

    $response = $this->getJson('/api/tags');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
});

test('can create a tag', function (): void {
    $payload = [
        'name' => 'Test Tag',
        'slug' => 'test-tag',
    ];

    $response = $this->postJson('/api/tags', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'name' => 'Test Tag',
                'slug' => 'test-tag',
            ],
        ]);

    $this->assertDatabaseHas('tags', [
        'name' => 'Test Tag',
        'slug' => 'test-tag',
    ]);
});

test('can show a tag', function (): void {
    $tag = Tag::factory()->create();

    $response = $this->getJson('/api/tags/' . $tag->slug);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ],
        ]);
});

test('can update a tag', function (): void {
    $tag = Tag::factory()->create();

    $payload = [
        'name' => 'Updated Tag',
        'slug' => 'updated-tag',
    ];

    $response = $this->putJson('/api/tags/' . $tag->slug, $payload);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'Updated Tag',
                'slug' => 'updated-tag',
            ],
        ]);

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'name' => 'Updated Tag',
        'slug' => 'updated-tag',
    ]);
});

test('can delete a tag', function (): void {
    $tag = Tag::factory()->create();

    $response = $this->deleteJson('/api/tags/' . $tag->slug);

    $response->assertStatus(204);
    $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
});

test('validates required fields when creating tag', function (): void {
    $response = $this->postJson('/api/tags', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'name',
            'slug',
        ]);
});

test('validates unique slug when creating tag', function (): void {
    $existingTag = Tag::factory()->create();

    $payload = [
        'name' => 'New Tag',
        'slug' => $existingTag->slug,
    ];

    $response = $this->postJson('/api/tags', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['slug']);
});
