<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Favorite;
use App\Models\User;
use Exception;

test('favorite has correct fillable attributes', function (): void {
    $favorite = new Favorite();

    expect($favorite->getFillable())->toEqual([
        'user_id',
        'event_id',
    ]);
});

test('favorite belongs to a user', function (): void {
    $favorite = Favorite::factory()->create();

    expect($favorite->user)->toBeInstanceOf(User::class);
});

test('favorite belongs to an event', function (): void {
    $favorite = Favorite::factory()->create();

    expect($favorite->event)->toBeInstanceOf(Event::class);
});

test('favorite factory creates valid favorite', function (): void {
    $favorite = Favorite::factory()->create();

    expect($favorite)->toBeInstanceOf(Favorite::class)
        ->and($favorite->user_id)->not->toBeEmpty()
        ->and($favorite->event_id)->not->toBeEmpty();
});

test('favorite enforces unique user and event combination', function (): void {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    Favorite::factory()->create([
        'user_id' => $user->id,
        'event_id' => $event->id,
    ]);

    expect(fn () => Favorite::factory()->create([
        'user_id' => $user->id,
        'event_id' => $event->id,
    ]))->toThrow(Exception::class);
});
