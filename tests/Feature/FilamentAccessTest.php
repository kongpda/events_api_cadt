<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    // Clear any cached config
    config()->clearBootedModels();
});

test('user with valid email domain can access filament panel', function (): void {
    $user = User::factory()->create([
        'email' => 'test@khable.com',
    ]);

    actingAs($user)
        ->get('/admin')
        ->assertStatus(200);
});

test('user with another valid email domain can access filament panel', function (): void {
    $user = User::factory()->create([
        'email' => 'test@gmail.com',
    ]);

    actingAs($user)
        ->get('/admin')
        ->assertStatus(200);
});

test('user with invalid email domain cannot access filament panel', function (): void {
    $user = User::factory()->create([
        'email' => 'test@invalid-domain.com',
    ]);

    actingAs($user)
        ->get('/admin')
        ->assertStatus(403);
});

test('guest cannot access filament panel', function () {
get('/admin')->assertStatus(302)->assertRedirect('/login');
    });
