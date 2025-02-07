<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Tag;
use Illuminate\Database\Seeder;

final class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Event::factory()->count(10)->create()->each(function ($event): void {
            $tags = Tag::inRandomOrder()->take(random_int(1, 3))->pluck('id');
            $event->tags()->attach($tags);
        });
    }
}
