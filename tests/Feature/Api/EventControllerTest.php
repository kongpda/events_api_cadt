<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Event;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create();
});

test('can list events', function (): void {
    Event::factory()->count(3)->create();

    $response = $this->getJson('/api/events');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
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
                    'created_at',
                    'updated_at',
                ],
            ],
            'links',
            'meta',
        ]);
});

test('can create an event', function (): void {
    $tags = Tag::factory(2)->create();
    $startDate = Carbon::now()->addDays(5);
    $endDate = $startDate->copy()->addDays(2);
    $registrationDeadline = $startDate->copy()->subDay();

    $payload = [
        'title' => 'Test Event',
        'slug' => 'test-event',
        'description' => 'Test event description',
        'address' => '123 Test St',
        'feature_image' => 'events/test.jpg',
        'start_date' => $startDate->toDateTimeString(),
        'end_date' => $endDate->toDateTimeString(),
        'category_id' => $this->category->id,
        'user_id' => $this->user->id,
        'participation_type' => 'paid',
        'capacity' => 100,
        'registration_deadline' => $registrationDeadline->toDateTimeString(),
        'registration_status' => 'open',
        'event_type' => 'hybrid',
        'online_url' => 'https://example.com/event',
        'tags' => $tags->pluck('id')->toArray(),
    ];

    $response = $this->postJson('/api/events', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'title' => 'Test Event',
                'slug' => 'test-event',
                'description' => 'Test event description',
                'participation_type' => 'paid',
                'registration_status' => 'open',
                'event_type' => 'hybrid',
            ],
        ]);

    $this->assertDatabaseHas('events', [
        'title' => 'Test Event',
        'slug' => 'test-event',
    ]);
});

test('can show an event', function (): void {
    $event = Event::factory()->create();

    $response = $this->getJson('/api/events/' . $event->slug);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $event->id,
                'title' => $event->title,
                'slug' => $event->slug,
            ],
        ]);
});

test('can update an event', function (): void {
    $event = Event::factory()->create();
    $newTags = Tag::factory(2)->create();

    $payload = [
        'title' => 'Updated Event',
        'slug' => 'updated-event',
        'description' => 'Updated description',
        'tags' => $newTags->pluck('id')->toArray(),
    ];

    $response = $this->putJson('/api/events/' . $event->slug, $payload);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'title' => 'Updated Event',
                'slug' => 'updated-event',
                'description' => 'Updated description',
            ],
        ]);

    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'title' => 'Updated Event',
        'slug' => 'updated-event',
    ]);
});

test('can delete an event', function (): void {
    $event = Event::factory()->create();

    $response = $this->deleteJson('/api/events/' . $event->slug);

    $response->assertStatus(204);
    $this->assertDatabaseMissing('events', ['id' => $event->id]);
});

test('validates required fields when creating event', function (): void {
    $response = $this->postJson('/api/events', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'title',
            'description',
            'address',
            'start_date',
            'end_date',
            'category_id',
            'user_id',
            'participation_type',
            'capacity',
            'registration_deadline',
            'registration_status',
            'event_type',
        ]);
});

test('validates date fields when creating event', function (): void {
    $payload = [
        'start_date' => 'invalid-date',
        'end_date' => 'invalid-date',
        'registration_deadline' => 'invalid-date',
    ];

    $response = $this->postJson('/api/events', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'start_date',
            'end_date',
            'registration_deadline',
        ]);
});

test('validates end date is after start date', function (): void {
    $startDate = Carbon::now()->addDays(5);
    $endDate = $startDate->copy()->subDay(); // End date before start date

    $payload = [
        'title' => 'Test Event',
        'description' => 'Test Description',
        'start_date' => $startDate->toDateTimeString(),
        'end_date' => $endDate->toDateTimeString(),
    ];

    $response = $this->postJson('/api/events', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['end_date']);
});

test('validates registration deadline is before start date', function (): void {
    $startDate = Carbon::now()->addDays(5);
    $registrationDeadline = $startDate->copy()->addDay(); // Deadline after start date

    $payload = [
        'title' => 'Test Event',
        'description' => 'Test Description',
        'start_date' => $startDate->toDateTimeString(),
        'registration_deadline' => $registrationDeadline->toDateTimeString(),
    ];

    $response = $this->postJson('/api/events', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['registration_deadline']);
});

test('validates online url when event type is online or hybrid', function (): void {
    $payload = [
        'event_type' => 'online',
        'online_url' => null,
    ];

    $response = $this->postJson('/api/events', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['online_url']);

    $payload['event_type'] = 'hybrid';
    $response = $this->postJson('/api/events', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['online_url']);
});

test('can filter events by status', function (): void {
    Event::factory()->create(['registration_status' => 'open']);
    Event::factory()->create(['registration_status' => 'closed']);
    Event::factory()->create(['registration_status' => 'full']);

    $response = $this->getJson('/api/events?status=open');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJson([
            'data' => [
                [
                    'registration_status' => 'open',
                ],
            ],
        ]);
});
