<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;

final class TicketSeeder extends Seeder
{
    public function run(): void
    {
        Ticket::factory()
            ->count(50)
            ->create();
    }
}
