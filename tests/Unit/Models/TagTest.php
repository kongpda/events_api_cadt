<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Tag;

test('tag has correct fillable attributes', function (): void {
    $tag = new Tag();

    expect($tag->getFillable())->toEqual([
        'name',
        'slug',
        'description',
        'is_active',
        'position',
    ]);
});

test('tag has correct casts', function (): void {
    $tag = new Tag();

    expect($tag->casts())->toEqual([
        'is_active' => 'boolean',
        'position' => 'integer',
    ]);
});

test('tag can have many events', function (): void {
    $tag = Tag::factory()->create();
    $events = Event::factory()->count(3)->create();

    $tag->events()->attach($events);

    expect($tag->events)->toHaveCount(3)
        ->each->toBeInstanceOf(Event::class);
});

test('tag uses slug for route key name', function (): void {
    $tag = new Tag();

    expect($tag->getRouteKeyName())->toBe('slug');
});

test('tag factory creates valid tag', function (): void {
    $tag = Tag::factory()->create();

    expect($tag)->toBeInstanceOf(Tag::class)
        ->and($tag->name)->not->toBeEmpty()
        ->and($tag->slug)->not->toBeEmpty()
        ->and($tag->description)->not->toBeEmpty()
        ->and($tag->is_active)->toBeBool()
        ->and($tag->position)->toBeInt();
});
