<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Event;
use App\Models\OrganizerProfile;
use App\Models\Tag;
use App\Models\User;
use App\Models\Venue;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class SeedProductionData extends Command
{
    protected $signature = 'app:seed-production-data';

    protected $description = 'Seed initial production data for venues, events, and related models';

    public function handle(): void
    {
        if (app()->environment('production') && ! $this->confirm('WARNING: You are in production environment. Are you absolutely sure you want to proceed?', false)) {
            $this->error('Operation cancelled.');

            return;
        }

        $this->info('Starting production data seeding...');

        try {
            DB::transaction(function (): void {
                $this->createCategories();
                $this->info('✓ Categories created');

                $this->createTags();
                $this->info('✓ Tags created');

                $venue = $this->createVenue();
                $this->info('✓ Venue created');

                // Get or create a test user for relationships
                $user = User::query()->firstOrCreate(
                    ['email' => 'test@example.com'],
                    [
                        'name' => 'Test User',
                        'password' => bcrypt('password'),
                        'email_verified_at' => now(),
                    ]
                );

                $this->createOrganizerProfile($user);
                $this->info('✓ Organizer profile created');

                $this->createMultipleEvents($user);
                $this->info('✓ Multiple events created');
            });

            $this->info('Production data seeding completed successfully!');
        } catch (Exception $exception) {
            $this->error('Error seeding data: ' . $exception->getMessage());
        }
    }

    private function createCategories(): void
    {
        $categories = [
            'Music',
            'Theatre',
            'Dance',
            'Comedy',
            'Opera',
        ];

        foreach ($categories as $category) {
            Category::query()->firstOrCreate(
                ['slug' => Str::slug($category)],
                ['name' => $category]
            );
        }
    }

    private function createTags(): void
    {
        $tags = [
            'Traditional',
            'Classical',
            'Contemporary',
            'Festival',
            'Live Show',
        ];

        foreach ($tags as $tag) {
            Tag::query()->firstOrCreate(
                ['slug' => Str::slug($tag)],
                ['name' => $tag]
            );
        }
    }

    private function createVenue(): Venue
    {
        return Venue::query()->firstOrCreate(
            ['name' => 'Sample Venue'],
            [
                'address' => '123 Main Street',
                'city' => 'Phnom Penh',
                'state' => 'Phnom Penh',
                'country' => 'Cambodia',
                'postal_code' => '12000',
                'capacity' => 500,
            ]
        );
    }

    private function createOrganizerProfile(User $user): OrganizerProfile
    {
        return OrganizerProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'organization_name' => 'Sample Organization',
                'description' => 'A sample organization for testing',
                'website' => 'https://example.com',
                'phone' => '+855 12 345 678',
                'address' => '123 Organization St',
                'city' => 'Phnom Penh',
                'state' => 'Phnom Penh',
                'country' => 'Cambodia',
                'postal_code' => '12000',
            ]
        );
    }

    private function createMultipleEvents(User $user): void
    {
        $categories = Category::all();
        $tags = Tag::all();
        $eventTypes = [
            'Festival', 'Exhibition', 'Performance',
            'Concert', 'Show', 'Gala',
            'Workshop', 'Conference', 'Showcase',
            'Competition',
        ];

        $eventAdjectives = [
            'Annual', 'International', 'Summer',
            'Winter', 'Spring', 'Grand',
            'Premier', 'Classic', 'Modern',
            'Traditional',
        ];

        for ($i = 1; $i <= 20; $i++) {
            $startDate = now()->addDays(rand(1, 365));

            // Generate more realistic event names
            $title = fake()->randomElement($eventAdjectives) . ' ' .
                    fake()->city() . ' ' .
                    fake()->randomElement($eventTypes);

            $event = Event::query()->create([
                'title' => $title,
                'slug' => Str::slug($title),
                'description' => fake()->paragraph(3),
                'address' => fake()->address(),
                'feature_image' => '@event-image-one.jpg',
                'start_date' => $startDate,
                'end_date' => $startDate->copy()->addHours(rand(2, 48)),
                'status' => 'published',
                'user_id' => $user->id,
            ]);

            // Attach random categories (1-3)
            $event->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->toArray()
            );

            // Attach random tags (1-5)
            $event->tags()->attach(
                $tags->random(rand(1, 5))->pluck('id')->toArray()
            );
        }
    }
}
