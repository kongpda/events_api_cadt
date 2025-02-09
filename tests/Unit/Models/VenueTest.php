<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Venue;

test('venue has correct fillable attributes', function (): void {
    $venue = new Venue();

    expect($venue->getFillable())->toEqual([
        'name',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'capacity',
    ]);
});

test('venue can have many events', function (): void {
    $venue = Venue::factory()->create();
    Event::factory()->count(3)->for($venue)->create();

    expect($venue->events)->toHaveCount(3)
        ->each->toBeInstanceOf(Event::class);
});

test('venue factory creates valid venue', function (): void {
    $venue = Venue::factory()->create();

    expect($venue)->toBeInstanceOf(Venue::class)
        ->and($venue->name)->not->toBeEmpty()
        ->and($venue->address)->not->toBeEmpty()
        ->and($venue->city)->not->toBeEmpty()
        ->and($venue->state)->not->toBeEmpty()
        ->and($venue->country)->not->toBeEmpty()
        ->and($venue->postal_code)->not->toBeEmpty()
        ->and($venue->capacity)->toBeInt();
});
