<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Share;
use Illuminate\Database\Seeder;

final class ShareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Share::factory()
            ->count(10)
            ->create();
    }
}
