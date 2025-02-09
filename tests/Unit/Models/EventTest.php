<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Event;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'start_date',
        'end_date',
        'registration_deadline',
        'capacity',
    ]);
});

test('event belongs to a user', function (): void {
    $event = Event::factory()->create();

    expect($event->user)->toBeInstanceOf(User::class);
});

test('event belongs to a venue', function (): void {
    $event = Event::factory()->create();

    expect($event->venue())->toBeInstanceOf(BelongsTo::class);
});

test('event belongs to a category', function (): void {
    $event = Event::factory()->create();

    expect($event->category)->toBeInstanceOf(Category::class);
});

test('event can have many tags', function (): void {
    $event = Event::factory()->create();
    $tags = Tag::factory(3)->create();

    $event->tags()->attach($tags);

    expect($event->tags)->toHaveCount(3)
        ->each->toBeInstanceOf(Tag::class);
});

test('event can have many tickets', function (): void {
    $event = Event::factory()->create();
    $tickets = Ticket::factory(3)->create(['event_id' => $event->id]);

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
        ->and($event->title)->not->toBeEmpty()
        ->and($event->slug)->not->toBeEmpty()
        ->and($event->description)->not->toBeEmpty()
        ->and($event->start_date)->toBeInstanceOf(Carbon\Carbon::class)
        ->and($event->end_date)->toBeInstanceOf(Carbon\Carbon::class)
        ->and($event->registration_deadline)->toBeInstanceOf(Carbon\Carbon::class)
        ->and($event->capacity)->toBeInt()
        ->and($event->participation_type)->toBeIn(['paid', 'free'])
        ->and($event->registration_status)->toBeIn(['open', 'closed', 'full'])
        ->and($event->event_type)->toBeIn(['in_person', 'online', 'hybrid']);
});

test('event dates are properly cast to carbon instances', function (): void {
    $event = Event::factory()->create();

    expect($event->start_date)->toBeInstanceOf(Carbon\Carbon::class)
        ->and($event->end_date)->toBeInstanceOf(Carbon\Carbon::class)
        ->and($event->registration_deadline)->toBeInstanceOf(Carbon\Carbon::class);
});
