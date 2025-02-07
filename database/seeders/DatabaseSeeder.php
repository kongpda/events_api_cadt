<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            // UserSeeder::class,
            // OrderSeeder::class,
            CategorySeeder::class,
            VenueSeeder::class,
            EventSeeder::class,
            TagSeeder::class,
            OrganizerProfileSeeder::class,
        ]);
        $this->createUser();
    }

    protected function createUser()
    {
        return User::factory()->create([
            // 'id' => $this->userID,
            'name' => 'da',
            'email' => 'da@khable.com',
            // 'is_admin' => true,
        ]);
    }
}
