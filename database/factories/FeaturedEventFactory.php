<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\FeaturedEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeaturedEvent>
 */
final class FeaturedEventFactory extends Factory
{
    protected $model = FeaturedEvent::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'order' => fake()->numberBetween(1, 100),
            'active_from' => now(),
            'active_until' => now()->addDays(30),
        ];
    }
}
