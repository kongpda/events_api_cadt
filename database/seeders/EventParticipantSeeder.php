<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EventParticipant;
use Illuminate\Database\Seeder;

final class EventParticipantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EventParticipant::factory()
            ->count(100)
            ->create();
    }
}
