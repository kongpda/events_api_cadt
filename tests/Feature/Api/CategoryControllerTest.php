<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

test('can list categories', function (): void {
    Category::factory()->count(3)->create();

    $response = $this->getJson('/api/categories');
    ray($response->getContent());

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
});

test('can create a category', function (): void {
    $payload = [
        'name' => 'Test Category',
        'slug' => 'test-category',
        'description' => 'Test category description',
    ];

    $response = $this->postJson('/api/categories', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'name' => 'Test Category',
                'slug' => 'test-category',
                'description' => 'Test category description',
            ],
        ]);

    $this->assertDatabaseHas('categories', [
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);
});

test('can show a category', function (): void {
    $category = Category::factory()->create();

    $response = $this->getJson('/api/categories/' . $category->slug);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ],
        ]);
});

test('can update a category', function (): void {
    $category = Category::factory()->create();

    $payload = [
        'name' => 'Updated Category',
        'slug' => 'updated-category',
        'description' => 'Updated description',
    ];

    $response = $this->putJson('/api/categories/' . $category->slug, $payload);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'Updated Category',
                'slug' => 'updated-category',
                'description' => 'Updated description',
            ],
        ]);

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Updated Category',
        'slug' => 'updated-category',
    ]);
});

test('can delete a category', function (): void {
    $category = Category::factory()->create();

    $response = $this->deleteJson('/api/categories/' . $category->slug);

    $response->assertStatus(204);
    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('validates required fields when creating category', function (): void {
    $response = $this->postJson('/api/categories', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'name',
            'slug',
        ]);
});

test('validates unique slug when creating category', function (): void {
    $existingCategory = Category::factory()->create();

    $payload = [
        'name' => 'New Category',
        'slug' => $existingCategory->slug,
        'description' => 'New description',
    ];

    $response = $this->postJson('/api/categories', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['slug']);
});
