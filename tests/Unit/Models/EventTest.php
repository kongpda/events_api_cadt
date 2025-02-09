<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;

test('event has correct fillable attributes', function (): void {
    $event = new Event();

    expect($event->getFillable())->toEqual([
        'title',
        'slug',
        'description',
        'address',
        'feature_image',
        'start_date',
        'end_date',
        'category_id',
        'user_id',
        'organizer_id',
        'participation_type',
        'capacity',
        'registration_deadline',
        'registration_status',
        'event_type',
        'online_url',
    ]);
});

test('event has correct casts', function (): void {
    $event = new Event();

    expect($event->getCasts())->toHaveKeys([
        'id',
        'start_date',
        'end_date',
        'registration_deadline',
        'capacity',
    ]);

    expect($event->getCasts())->toMatchArray([
        'id' => 'string',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'registration_deadline' => 'datetime',
        'capacity' => 'integer',
    ]);
});

test('event belongs to a user', function (): void {
    $user = User::factory()->create();
    $event = Event::factory()->for($user)->create();

    expect($event->user)->toBeInstanceOf(User::class)
        ->and($event->user->id)->toBe($user->id);
});

test('event belongs to a category', function (): void {
    $category = Category::factory()->create();
    $event = Event::factory()->for($category)->create();

    expect($event->category)->toBeInstanceOf(Category::class)
        ->and($event->category->id)->toBe($category->id);
});

test('event belongs to an organizer', function (): void {
    $organizer = Organizer::factory()->create();
    $event = Event::factory()->for($organizer)->create();

    expect($event->organizer)->toBeInstanceOf(Organizer::class)
        ->and($event->organizer->id)->toBe($organizer->id);
});

test('event can have many tags', function (): void {
    $event = Event::factory()->create();
    $tags = Tag::factory()->count(3)->create();

    $event->tags()->attach($tags);

    expect($event->tags)->toHaveCount(3)
        ->each->toBeInstanceOf(Tag::class);
});

test('event can have many tickets', function (): void {
    $event = Event::factory()->create();
    $tickets = Ticket::factory()->count(3)->for($event)->create();

    expect($event->tickets)->toHaveCount(3)
        ->each->toBeInstanceOf(Ticket::class);
});

test('event uses slug for route key name', function (): void {
    $event = new Event();

    expect($event->getRouteKeyName())->toBe('slug');
});

test('event has correct participation types', function (): void {
    $event = new Event();

    expect($event->getParticipationTypes())->toBe([
        'paid' => 'Paid',
        'free' => 'Free',
    ]);
});

test('event has correct registration statuses', function (): void {
    $event = new Event();

    expect($event->getRegistrationStatuses())->toBe([
        'open' => 'Open',
        'closed' => 'Closed',
        'full' => 'Full',
    ]);
});

test('event has correct event types', function (): void {
    $event = new Event();

    expect($event->getEventTypes())->toBe([
        'in_person' => 'In Person',
        'online' => 'Online',
        'hybrid' => 'Hybrid',
    ]);
});

test('scope status filters events by status', function (): void {
    Event::factory()->create(['registration_status' => 'open']);
    Event::factory()->create(['registration_status' => 'closed']);
    Event::factory()->create(['registration_status' => 'full']);

    $openEvents = Event::status('open')->get();

    expect($openEvents)->toHaveCount(1)
        ->first()->registration_status->toBe('open');
});

test('event factory creates valid event', function (): void {
    $event = Event::factory()->create();

    expect($event)->toBeInstanceOf(Event::class)
        ->and($event->id)->not->toBeEmpty()
        ->and($event->title)->not->toBeEmpty()
        ->and($event->slug)->not->toBeEmpty()
        ->and($event->description)->not->toBeEmpty();
});

test('event dates are properly cast to carbon instances', function (): void {
    $event = Event::factory()->create();

    expect($event->start_date)->toBeInstanceOf(Carbon::class)
        ->and($event->end_date)->toBeInstanceOf(Carbon::class)
        ->and($event->registration_deadline)->toBeInstanceOf(Carbon::class);
});
