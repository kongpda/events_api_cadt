<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;

test('order has correct fillable attributes', function (): void {
    $order = new Order();

    expect($order->getFillable())->toEqual([
        'event_id',
        'user_id',
        'total_amount',
        'status',
        'payment_method',
        'payment_status',
        'payment_id',
    ]);
});

test('order has correct casts', function (): void {
    $order = new Order();

    expect($order->getCasts())->toHaveKeys([
        'total_amount',
    ]);
});

test('order belongs to an event', function (): void {
    $order = Order::factory()->create();

    expect($order->event)->toBeInstanceOf(Event::class);
});

test('order belongs to a user', function (): void {
    $order = Order::factory()->create();

    expect($order->user)->toBeInstanceOf(User::class);
});

test('order can have many tickets', function (): void {
    $order = Order::factory()->create();
    Ticket::factory()->count(3)->for($order)->create();

    expect($order->tickets)->toHaveCount(3)
        ->each->toBeInstanceOf(Ticket::class);
});

test('order factory creates valid order', function (): void {
    $order = Order::factory()->create();

    expect($order)->toBeInstanceOf(Order::class)
        ->and($order->total_amount)->toBeNumeric()
        ->and($order->status)->not->toBeEmpty()
        ->and($order->payment_method)->not->toBeEmpty()
        ->and($order->payment_status)->not->toBeEmpty()
        ->and($order->payment_id)->not->toBeEmpty();
});
