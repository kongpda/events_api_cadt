<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\OrganizerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

final class OrganizerProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory(10)->create()->each(function ($user): void {
            OrganizerProfile::factory()->create(['user_id' => $user->id]);
        });
    }
}
