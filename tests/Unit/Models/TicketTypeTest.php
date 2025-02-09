<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;

test('ticket type has correct fillable attributes', function (): void {
    $ticketType = new TicketType();

    expect($ticketType->getFillable())->toEqual([
        'event_id',
        'name',
        'price',
        'quantity',
        'description',
        'status',
    ]);
});

test('ticket type has correct casts', function (): void {
    $ticketType = new TicketType();

    expect($ticketType->getCasts())->toHaveKeys([
        'price',
        'quantity',
    ]);
});

test('ticket type belongs to an event', function (): void {
    $ticketType = TicketType::factory()->create();

    expect($ticketType->event)->toBeInstanceOf(Event::class);
});

test('ticket type can have many tickets', function (): void {
    $ticketType = TicketType::factory()->create();
    Ticket::factory()->count(3)->for($ticketType)->create();

    expect($ticketType->tickets)->toHaveCount(3)
        ->each->toBeInstanceOf(Ticket::class);
});

test('ticket type factory creates valid ticket type', function (): void {
    $ticketType = TicketType::factory()->create();

    expect($ticketType)->toBeInstanceOf(TicketType::class)
        ->and($ticketType->name)->not->toBeEmpty()
        ->and($ticketType->description)->not->toBeEmpty()
        ->and($ticketType->price)->toBeNumeric()
        ->and($ticketType->quantity)->toBeInt()
        ->and($ticketType->status)->not->toBeEmpty();
});
