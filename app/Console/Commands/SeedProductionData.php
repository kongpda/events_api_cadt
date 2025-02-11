<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\Share;
use App\Models\Tag;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Venue;
use App\Support\Permissions\PermissionManager;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

final class SeedProductionData extends Command
{
    protected $signature = 'app:seed-production-data';

    protected $description = 'Seed initial production data for venues, events, and related models';

    private array $eventTitles = [
        'Cambodia International Music & Arts Festival 2024',
        'Phnom Penh Night Market Food Festival',
        'ASEAN Digital Innovation Summit 2024',
        'Traditional Khmer Dance Showcase',
        'Cambodia Real Estate & Property Expo',
        'Siem Reap International Half Marathon',
        'Cambodia Investment Forum 2024',
        'Angkor Photography Festival & Workshops',
        'Mekong Tourism Forum',
        'Cambodia International Film Festival',
        'TEDx Phnom Penh 2024',
        'Cambodia Architect & Design Week',
        'Phnom Penh Street Art Festival',
        'Cambodia Tech Summit 2024',
        'Kampot Writers & Readers Festival',
        'Cambodia Fashion Week',
        'ASEAN Youth Leadership Conference',
        'Siem Reap Food & Craft Market',
        'Cambodia Startup Summit 2024',
        'Angkor Wat International Half Marathon',
    ];

    private array $eventDescriptions = [
        'The largest music and arts festival in Cambodia, featuring both international and local artists. Experience three days of live performances, art installations, and cultural exhibitions.',
        'Discover the best of Cambodian street food and local delicacies. Over 100 vendors, live cooking demonstrations, and cultural performances.',
        'Join tech leaders from across ASEAN to discuss digital transformation, innovation, and the future of technology in Southeast Asia.',
        'Experience the beauty and grace of traditional Khmer dance, performed by the Royal Ballet of Cambodia. A UNESCO Intangible Cultural Heritage event.',
        'The premier real estate exhibition in Cambodia, featuring major developers, property investments, and industry insights.',
        'Join thousands of runners in this scenic race through the historic city of Siem Reap, supporting local community projects.',
        "Connect with leading investors and business leaders to explore investment opportunities in Cambodia's growing economy.",
        "A celebration of photography featuring exhibitions, workshops, and photo tours around Angkor's historic temples.",
        'The leading tourism conference in the Mekong region, focusing on sustainable tourism development and regional cooperation.',
        'Showcasing the best of Cambodian and international cinema, with special screenings, director talks, and workshops.',
        "Inspiring talks and presentations from Cambodia's leading thinkers, innovators, and change-makers.",
        'A week-long celebration of architecture and design, featuring exhibitions, talks, and workshops from leading professionals.',
        'Transform the streets of Phnom Penh into an open-air gallery with local and international street artists.',
        "Cambodia's largest technology conference, bringing together startups, tech companies, and digital innovators.",
        'A unique literary festival celebrating Southeast Asian literature, featuring authors, workshops, and cultural events.',
        'The premier fashion event in Cambodia, showcasing local designers and international brands.',
        'Empowering young leaders from across ASEAN through workshops, networking, and skill-building sessions.',
        'A vibrant market featuring local artisans, craftspeople, and food vendors in the heart of Siem Reap.',
        'The largest gathering of startups, investors, and entrepreneurs in Cambodia.',
        "One of Asia's premier destination marathons, running through the ancient temples of Angkor.",
    ];

    private array $venues = [
        [
            'name' => 'Koh Pich Convention & Exhibition Centre',
            'address' => 'Diamond Island, Tonle Bassac',
            'city' => 'Phnom Penh',
            'state' => 'Phnom Penh',
            'country' => 'Cambodia',
            'postal_code' => '12000',
            'capacity' => 5000,
        ],
        [
            'name' => 'National Olympic Stadium',
            'address' => 'Charles de Gaulle Blvd (217)',
            'city' => 'Phnom Penh',
            'state' => 'Phnom Penh',
            'country' => 'Cambodia',
            'postal_code' => '12000',
            'capacity' => 50000,
        ],
        [
            'name' => 'Sokha Phnom Penh Hotel',
            'address' => 'Chroy Changvar Bridge, Tonle Sap Street',
            'city' => 'Phnom Penh',
            'state' => 'Phnom Penh',
            'country' => 'Cambodia',
            'postal_code' => '12000',
            'capacity' => 1500,
        ],
        [
            'name' => 'Factory Phnom Penh',
            'address' => '#1159, National Assembly Street',
            'city' => 'Phnom Penh',
            'state' => 'Phnom Penh',
            'country' => 'Cambodia',
            'postal_code' => '12000',
            'capacity' => 2000,
        ],
        [
            'name' => 'Raffles Hotel Le Royal',
            'address' => '92 Rukhak Vithei Daun Penh',
            'city' => 'Phnom Penh',
            'state' => 'Phnom Penh',
            'country' => 'Cambodia',
            'postal_code' => '12000',
            'capacity' => 800,
        ],
    ];

    public function handle(): void
    {
        if (app()->environment('production') && ! $this->confirm('WARNING: You are in production environment. Are you absolutely sure you want to proceed?', false)) {
            $this->error('Operation cancelled.');

            return;
        }

        $this->info('Starting production data seeding...');

        try {
            DB::transaction(function (): void {
                $this->createRolesAndPermissions();
                $this->info('✓ Roles and permissions created');

                $this->createCategories();
                $this->info('✓ Categories created');

                $this->createTags();
                $this->info('✓ Tags created');

                $venues = $this->createVenues();
                $this->info('✓ Venues created');

                // Get or create a test user for relationships
                $user = User::query()->firstOrCreate(
                    ['email' => 'admin@herdapp.com'],
                    [
                        'name' => 'Admin User',
                        'password' => bcrypt('password'),
                        'email_verified_at' => now(),
                    ],
                );

                // Create some test users for favorites and shares
                $testUsers = $this->createTestUsers();
                $this->info('✓ Test users created');

                $organizer = $this->createOrganizer($user);
                $this->info('✓ Organizer created');

                $events = $this->createMultipleEvents($user, $organizer, $venues);
                $this->info('✓ Multiple events created');

                $this->createTicketTypes($events);
                $this->info('✓ Ticket types created');

                $this->createFavorites($testUsers, $events);
                $this->info('✓ Favorites created');

                $this->createShares($testUsers, $events);
                $this->info('✓ Shares created');
            });

            $this->info('Production data seeding completed successfully!');
        } catch (Exception $exception) {
            $this->error('Error seeding data: ' . $exception->getMessage());
        }
    }

    private function createRolesAndPermissions(): void
    {
        // Create permissions and roles
        PermissionManager::createPermissions();
        PermissionManager::createRoles();

        // Assign super-admin role to the admin user
        $adminUser = User::query()->where('email', 'admin@herdapp.com')->first();
        if ($adminUser) {
            $adminUser->assignRole('super-admin');
        }

        // Assign organizer role to test users
        $testUsers = User::query()->whereIn('email', [
            'john@example.com',
            'jane@example.com',
        ])->get();

        foreach ($testUsers as $user) {
            $user->assignRole('organizer');
        }

        // Assign regular user role to remaining test users
        $regularUsers = User::query()->whereIn('email', [
            'bob@example.com',
            'alice@example.com',
            'david@example.com',
        ])->get();

        foreach ($regularUsers as $user) {
            $user->assignRole('user');
        }
    }

    private function createCategories(): void
    {
        $categories = [
            'Arts & Culture' => 'Art exhibitions, cultural shows, and performances',
            'Business & Finance' => 'Conferences, networking events, and workshops',
            'Community' => 'Local events, fundraisers, and social gatherings',
            'Education' => 'Workshops, seminars, and training sessions',
            'Entertainment' => 'Concerts, shows, and performances',
            'Food & Drink' => 'Food festivals, tastings, and culinary events',
            'Health & Wellness' => 'Fitness classes, wellness workshops, and health seminars',
            'Sports & Recreation' => 'Sporting events, tournaments, and outdoor activities',
            'Technology' => 'Tech conferences, hackathons, and digital events',
            'Travel & Outdoor' => 'Travel expos, outdoor festivals, and adventures',
        ];

        foreach ($categories as $name => $description) {
            Category::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $description,
                ],
            );
        }
    }

    private function createTags(): void
    {
        $tags = [
            'Featured' => 'Highlighted special events',
            'Early Bird' => 'Early bird tickets available',
            'Family Friendly' => 'Suitable for all ages',
            'Business' => 'Professional and business events',
            'Networking' => 'Events focused on making connections',
            'Workshop' => 'Hands-on learning experiences',
            'Conference' => 'Large-scale professional gatherings',
            'Performance' => 'Live shows and performances',
            'Exhibition' => 'Art and cultural exhibitions',
            'Local Event' => 'Events by local organizers',
            'International' => 'Events with international participants',
            'Weekend' => 'Events happening on weekends',
            'Evening' => 'Evening and night events',
            'All Day' => 'Full-day events and activities',
            'Free Entry' => 'Events with free admission',
        ];

        foreach ($tags as $name => $description) {
            Tag::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $description,
                    'is_active' => true,
                    'position' => 0,
                ],
            );
        }
    }

    private function createVenues(): array
    {
        $venues = [];
        foreach ($this->venues as $venueData) {
            $venue = Venue::query()->firstOrCreate(
                ['name' => $venueData['name']],
                [
                    'address' => $venueData['address'],
                    'city' => $venueData['city'],
                    'state' => $venueData['state'],
                    'country' => $venueData['country'],
                    'postal_code' => $venueData['postal_code'],
                    'capacity' => $venueData['capacity'],
                ],
            );
            $venues[] = $venue;
        }

        return $venues;
    }

    private function createOrganizer(User $user): Organizer
    {
        return Organizer::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => 'Herd Events Cambodia',
                'slug' => 'herd-events-cambodia',
                'email' => 'events@herdapp.com',
                'phone' => '+855 93 666 888',
                'description' => "Cambodia's premier event management and ticketing platform. We specialize in organizing and promoting a wide range of events, from cultural festivals to business conferences, connecting event organizers with their audience across the country.",
                'website' => 'https://herdapp.com',
                'address' => '#11, Street 254, Sangkat Chaktomuk, Khan Daun Penh, Phnom Penh',
                'social_media' => json_encode([
                    'facebook' => 'https://facebook.com/herdevents',
                    'instagram' => 'https://instagram.com/herdevents',
                    'linkedin' => 'https://linkedin.com/company/herdevents',
                    'telegram' => 'https://t.me/herdevents',
                ]),
                'logo' => 'https://herdapp.com/images/logo.png',
                'is_verified' => true,
            ],
        );
    }

    private function createMultipleEvents(User $user, Organizer $organizer, array $venues): array
    {
        $categories = Category::all();
        $tags = Tag::all();
        $events = [];

        $eventTypes = ['in_person', 'online', 'hybrid'];
        $participationTypes = ['paid', 'free'];
        $registrationStatuses = ['open', 'closed'];

        for ($i = 1; $i <= 20; $i++) {
            $startDate = now()->addDays(random_int(1, 365));
            $title = $this->eventTitles[$i - 1];
            $description = $this->eventDescriptions[$i - 1];
            $category = $categories->random();
            $venue = $venues[array_rand($venues)];

            $event = Event::query()->create([
                'title' => $title,
                'slug' => Str::slug($title),
                'description' => $description,
                'address' => $venue->address,
                'feature_image' => 'https://picsum.photos/800/600',
                'start_date' => $startDate,
                'end_date' => $startDate->copy()->addHours(random_int(2, 48)),
                'user_id' => $user->id,
                'organizer_id' => $organizer->id,
                'category_id' => $category->id,
                'participation_type' => $participationTypes[array_rand($participationTypes)],
                'capacity' => $venue->capacity,
                'registration_deadline' => $startDate->copy()->subDays(random_int(1, 7)),
                'registration_status' => $registrationStatuses[array_rand($registrationStatuses)],
                'event_type' => $eventTypes[array_rand($eventTypes)],
                'online_url' => fake()->url(),
            ]);

            $event->tags()->attach(
                $tags->random(random_int(1, 5))->pluck('id')->toArray(),
            );

            $events[] = $event;
        }

        return $events;
    }

    private function createTicketTypes(array $events): void
    {
        $ticketTypes = [
            'VIP' => [
                'price' => 100,
                'description' => 'VIP access with special perks',
                'quantity' => 50,
            ],
            'Regular' => [
                'price' => 50,
                'description' => 'Standard admission ticket',
                'quantity' => 200,
            ],
            'Early Bird' => [
                'price' => 30,
                'description' => 'Limited early bird tickets',
                'quantity' => 100,
            ],
        ];

        foreach ($events as $event) {
            foreach ($ticketTypes as $name => $details) {
                TicketType::query()->create([
                    'event_id' => $event->id,
                    'created_by' => $event->user_id,
                    'name' => $name,
                    'description' => $details['description'],
                    'price' => $details['price'],
                    'quantity' => $details['quantity'],
                    'status' => 'available',
                ]);
            }
        }
    }

    private function createTestUsers(): array
    {
        $users = [];
        $testUsers = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'profile' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'birth_date' => '1990-01-15',
                    'phone' => '+855 12 345 678',
                    'bio' => 'Tech enthusiast and event organizer',
                    'social_links' => [
                        'twitter' => 'https://twitter.com/johndoe',
                        'linkedin' => 'https://linkedin.com/in/johndoe',
                    ],
                ],
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'profile' => [
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'birth_date' => '1992-03-20',
                    'phone' => '+855 12 345 679',
                    'bio' => 'Digital marketing specialist and event planner',
                    'social_links' => [
                        'twitter' => 'https://twitter.com/janesmith',
                        'linkedin' => 'https://linkedin.com/in/janesmith',
                    ],
                ],
            ],
            [
                'name' => 'Bob Wilson',
                'email' => 'bob@example.com',
                'profile' => [
                    'first_name' => 'Bob',
                    'last_name' => 'Wilson',
                    'birth_date' => '1988-07-10',
                    'phone' => '+855 12 345 680',
                    'bio' => 'Community manager and event enthusiast',
                    'social_links' => [
                        'twitter' => 'https://twitter.com/bobwilson',
                        'linkedin' => 'https://linkedin.com/in/bobwilson',
                    ],
                ],
            ],
            [
                'name' => 'Alice Brown',
                'email' => 'alice@example.com',
                'profile' => [
                    'first_name' => 'Alice',
                    'last_name' => 'Brown',
                    'birth_date' => '1995-11-25',
                    'phone' => '+855 12 345 681',
                    'bio' => 'Event photographer and social media manager',
                    'social_links' => [
                        'twitter' => 'https://twitter.com/alicebrown',
                        'linkedin' => 'https://linkedin.com/in/alicebrown',
                    ],
                ],
            ],
            [
                'name' => 'David Lee',
                'email' => 'david@example.com',
                'profile' => [
                    'first_name' => 'David',
                    'last_name' => 'Lee',
                    'birth_date' => '1991-09-05',
                    'phone' => '+855 12 345 682',
                    'bio' => 'Event technology consultant',
                    'social_links' => [
                        'twitter' => 'https://twitter.com/davidlee',
                        'linkedin' => 'https://linkedin.com/in/davidlee',
                    ],
                ],
            ],
        ];

        foreach ($testUsers as $userData) {
            $user = User::query()->firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ],
            );

            // Update or create profile with specific data
            if ($user->profile) {
                $user->profile->update($userData['profile']);
            } else {
                $user->profile()->create($userData['profile']);
            }

            $users[] = $user;
        }

        return $users;
    }

    private function createFavorites(array $users, array $events): void
    {
        foreach ($users as $user) {
            // Each user favorites 3-8 random events
            $randomEvents = collect($events)->random(random_int(3, 8));

            // Use the relationship instead of the Favorite model
            foreach ($randomEvents as $event) {
                $user->favoriteEvents()->syncWithoutDetaching([$event->id => [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]]);
            }
        }
    }

    private function createShares(array $users, array $events): void
    {
        $platforms = ['facebook', 'twitter', 'telegram', 'whatsapp', 'email'];

        foreach ($users as $user) {
            // Each user shares 2-5 random events
            $randomEvents = collect($events)->random(random_int(2, 5));
            foreach ($randomEvents as $event) {
                // Each share includes 1-3 random platforms
                $selectedPlatforms = collect($platforms)->random(random_int(1, 3))->all();
                $shareUrls = [];
                foreach ($selectedPlatforms as $platform) {
                    $shareUrls[$platform] = match ($platform) {
                        'facebook' => 'https://facebook.com/sharer/sharer.php?u=https://herdapp.com/events/' . $event->slug,
                        'twitter' => sprintf('https://twitter.com/intent/tweet?url=https://herdapp.com/events/%s&text=', $event->slug) . urlencode((string) $event->title),
                        'telegram' => sprintf('https://t.me/share/url?url=https://herdapp.com/events/%s&text=', $event->slug) . urlencode((string) $event->title),
                        'whatsapp' => 'https://wa.me/?text=' . urlencode(sprintf('%s - https://herdapp.com/events/%s', $event->title, $event->slug)),
                        'email' => 'mailto:?subject=' . urlencode((string) $event->title) . '&body=' . urlencode('Check out this event: https://herdapp.com/events/' . $event->slug),
                    };
                }

                Share::query()->create([
                    'user_id' => $user->id,
                    'event_id' => $event->id,
                    'platform' => json_encode($selectedPlatforms),
                    'share_url' => json_encode($shareUrls),
                ]);
            }
        }
    }
}
