<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Database\Seeder;

final class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::factory(3)->create();
        $events = Event::factory(5)->create();

        foreach ($users as $user) {
            $events->random(2)->each(function ($event) use ($user): void {
                Favorite::factory()->create([
                    'user_id' => $user->id,
                    'event_id' => $event->id,
                ]);
            });
        }
    }
}
