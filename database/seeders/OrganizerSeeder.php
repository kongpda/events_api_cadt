<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Organizer;
use App\Models\User;
use Illuminate\Database\Seeder;

final class OrganizerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::factory(3)->create();

        foreach ($users as $user) {
            Organizer::factory()->create([
                'user_id' => $user->id,
                'is_verified' => true,
            ]);
        }

        // Create some organizers without users
        Organizer::factory(2)->create([
            'user_id' => null,
            'is_verified' => false,
        ]);
    }
}
