<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Share;
use App\Models\User;

test('share has correct fillable attributes', function (): void {
    $share = new Share();

    expect($share->getFillable())->toEqual([
        'user_id',
        'event_id',
        'platform',
        'share_url',
    ]);
});

test('share has correct casts', function (): void {
    $share = new Share();

    expect($share->getCasts())->toEqual([
        'id' => 'string',
        'user_id' => 'string',
        'event_id' => 'string',
        'platform' => 'array',
        'share_url' => 'array',
    ]);
});

test('share belongs to a user', function (): void {
    $share = Share::factory()->create();

    expect($share->user)->toBeInstanceOf(User::class);
});

test('share belongs to an event', function (): void {
    $share = Share::factory()->create();

    expect($share->event)->toBeInstanceOf(Event::class);
});

test('share factory creates valid share', function (): void {
    $share = Share::factory()->create();

    expect($share)->toBeInstanceOf(Share::class)
        ->and($share->user_id)->not->toBeEmpty()
        ->and($share->event_id)->not->toBeEmpty()
        ->and($share->platform)->toBeArray()
        ->and($share->share_url)->toBeArray();
});

test('share properly formats platform and url', function (): void {
    $share = Share::factory()->create([
        'platform' => ['facebook', 'twitter'],
        'share_url' => [
            'facebook' => 'https://facebook.com/share',
            'twitter' => 'https://twitter.com/intent/tweet',
        ],
    ]);

    expect($share->platform)->toBe(['facebook', 'twitter'])
        ->and($share->share_url)->toBe([
            'facebook' => 'https://facebook.com/share',
            'twitter' => 'https://twitter.com/intent/tweet',
        ]);
});
