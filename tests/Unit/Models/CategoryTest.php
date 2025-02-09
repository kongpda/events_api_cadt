<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Event;

test('category has correct fillable attributes', function (): void {
    $category = new Category();

    expect($category->getFillable())->toEqual([
        'name',
        'slug',
        'description',
    ]);
});

test('category can have many events', function (): void {
    $category = Category::factory()->create();
    Event::factory()->count(3)->for($category)->create();

    expect($category->events)->toHaveCount(3)
        ->each->toBeInstanceOf(Event::class);
});

test('category uses slug for route key name', function (): void {
    $category = new Category();

    expect($category->getRouteKeyName())->toBe('slug');
});

test('category factory creates valid category', function (): void {
    $category = Category::factory()->create();

    expect($category)->toBeInstanceOf(Category::class)
        ->and($category->name)->not->toBeEmpty()
        ->and($category->slug)->not->toBeEmpty()
        ->and($category->description)->not->toBeEmpty();
});
