<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;

test('ticket has correct fillable attributes', function (): void {
    $ticket = new Ticket();

    expect($ticket->getFillable())->toEqual([
        'event_id',
        'ticket_type_id',
        'order_id',
        'user_id',
        'status',
        'price',
        'code',
    ]);
});

test('ticket has correct casts', function (): void {
    $ticket = new Ticket();

    expect($ticket->getCasts())->toHaveKeys([
        'price',
    ]);
});

test('ticket belongs to an event', function (): void {
    $ticket = Ticket::factory()->create();

    expect($ticket->event)->toBeInstanceOf(Event::class);
});

test('ticket belongs to a ticket type', function (): void {
    $ticket = Ticket::factory()->create();

    expect($ticket->ticketType)->toBeInstanceOf(TicketType::class);
});

test('ticket belongs to an order', function (): void {
    $ticket = Ticket::factory()->create();

    expect($ticket->order)->toBeInstanceOf(Order::class);
});

test('ticket belongs to a user', function (): void {
    $ticket = Ticket::factory()->create();

    expect($ticket->user)->toBeInstanceOf(User::class);
});

test('ticket factory creates valid ticket', function (): void {
    $ticket = Ticket::factory()->create();

    expect($ticket)->toBeInstanceOf(Ticket::class)
        ->and($ticket->status)->not->toBeEmpty()
        ->and($ticket->price)->toBeNumeric()
        ->and($ticket->code)->not->toBeEmpty();
});
