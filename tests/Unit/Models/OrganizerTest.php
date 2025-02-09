<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Organizer;
use App\Models\User;

test('organizer has correct fillable attributes', function (): void {
    $organizer = new Organizer();

    expect($organizer->getFillable())->toEqual([
        'user_id',
        'name',
        'slug',
        'email',
        'phone',
        'description',
        'website',
        'address',
        'social_media',
        'logo',
        'is_verified',
    ]);
});

test('organizer has correct casts', function (): void {
    $organizer = new Organizer();

    expect($organizer->getCasts())->toHaveKeys([
        'id',
        'is_verified',
        'social_media',
    ]);
});

test('organizer belongs to a user', function (): void {
    $organizer = Organizer::factory()->create();

    expect($organizer->user)->toBeInstanceOf(User::class);
});

test('organizer can have many events', function (): void {
    $organizer = Organizer::factory()->create();
    Event::factory()->count(3)->for($organizer)->create();

    expect($organizer->events)->toHaveCount(3)
        ->each->toBeInstanceOf(Event::class);
});

test('organizer uses slug for route key name', function (): void {
    $organizer = new Organizer();

    expect($organizer->getRouteKeyName())->toBe('slug');
});

test('organizer factory creates valid organizer', function (): void {
    $organizer = Organizer::factory()->create();

    expect($organizer)->toBeInstanceOf(Organizer::class)
        ->and($organizer->name)->not->toBeEmpty()
        ->and($organizer->slug)->not->toBeEmpty()
        ->and($organizer->email)->not->toBeEmpty()
        ->and($organizer->phone)->not->toBeEmpty()
        ->and($organizer->description)->not->toBeEmpty()
        ->and($organizer->website)->not->toBeEmpty()
        ->and($organizer->address)->not->toBeEmpty()
        ->and($organizer->social_media)->toBeArray()
        ->and($organizer->logo)->not->toBeEmpty()
        ->and($organizer->is_verified)->toBeBool();
});

test('organizer generates slug from name', function (): void {
    $organizer = Organizer::factory()->create(['name' => 'Test Organizer']);

    expect($organizer->slug)->toBe('test-organizer');
});
