<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;

final class UserProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create profiles for existing users that don't have one
        User::whereDoesntHave('profile')->each(function (User $user): void {
            UserProfile::factory()->create(['user_id' => $user->id]);
        });

        // Create some users with profiles
        UserProfile::factory(10)->create();
    }
}
