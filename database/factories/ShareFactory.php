<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\Share;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Share>
 */
final class ShareFactory extends Factory
{
    protected $model = Share::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $platforms = ['facebook', 'twitter', 'linkedin', 'whatsapp'];

        return [
            'user_id' => User::factory(),
            'event_id' => Event::factory(),
            'platform' => fake()->randomElement($platforms),
            'share_url' => fake()->url(),
        ];
    }
}
