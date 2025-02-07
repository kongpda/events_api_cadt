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

                $this->createEvent($venue, $user);
                $this->info('✓ Event created');
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

    private function createEvent(Venue $venue, User $user): Event
    {
        $event = Event::query()->firstOrCreate(
            ['slug' => 'sample-event'],
            [
                'title' => 'Sample Event',
                'description' => 'A sample event for testing',
                'feature_image' => 'sample.jpg',
                'content' => ['description' => 'Sample content'],
                'action_content' => ['button' => 'Buy Tickets'],
                'status' => 'published',
                'user_id' => $user->id,
                'venue_id' => $venue->id,
            ]
        );

        // Attach relationships
        $event->categories()->sync([Category::first()->id]);
        $event->tags()->sync([Tag::first()->id]);

        return $event;
    }
}
