<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Organizer;
use Illuminate\Database\Seeder;

final class OrganizerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Organizer::factory()->count(10)->create();
    }
}
