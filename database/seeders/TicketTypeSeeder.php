<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TicketType;
use Illuminate\Database\Seeder;

final class TicketTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TicketType::factory()
            ->count(10)
            ->create();
    }
}
