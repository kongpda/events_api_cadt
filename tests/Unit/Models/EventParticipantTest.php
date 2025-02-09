<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\User;
use Carbon\Carbon;
use Exception;

test('event participant has correct fillable attributes', function (): void {
    $participant = new EventParticipant();

    expect($participant->getFillable())->toEqual([
        'event_id',
        'user_id',
        'role',
        'status',
        'check_in_time',
        'check_out_time',
        'notes',
    ]);
});

test('event participant has correct casts', function (): void {
    $participant = new EventParticipant();

    expect($participant->getCasts())->toHaveKeys([
        'id',
        'check_in_time',
        'check_out_time',
    ]);
});

test('event participant belongs to a user', function (): void {
    $participant = EventParticipant::factory()->create();

    expect($participant->user)->toBeInstanceOf(User::class);
});

test('event participant belongs to an event', function (): void {
    $participant = EventParticipant::factory()->create();

    expect($participant->event)->toBeInstanceOf(Event::class);
});

test('event participant factory creates valid participant', function (): void {
    $participant = EventParticipant::factory()->create();

    expect($participant)->toBeInstanceOf(EventParticipant::class)
        ->and($participant->role)->not->toBeEmpty()
        ->and($participant->status)->not->toBeEmpty()
        ->and($participant->notes)->not->toBeEmpty();
});

test('event participant properly casts check in and out times', function (): void {
    $participant = EventParticipant::factory()->create([
        'check_in_time' => now(),
        'check_out_time' => now()->addHours(2),
    ]);

    expect($participant->check_in_time)->toBeInstanceOf(Carbon::class)
        ->and($participant->check_out_time)->toBeInstanceOf(Carbon::class);
});

test('event participant enforces unique user and event combination', function (): void {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    EventParticipant::factory()->create([
        'user_id' => $user->id,
        'event_id' => $event->id,
    ]);

    expect(fn () => EventParticipant::factory()->create([
        'user_id' => $user->id,
        'event_id' => $event->id,
    ]))->toThrow(Exception::class);
});
