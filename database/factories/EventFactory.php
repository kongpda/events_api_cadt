<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Event;
use App\Models\Tag;
use App\Models\User;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
final class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = Carbon::instance($this->faker->dateTimeBetween('now', '+1 month'));
        $endDate = Carbon::instance($this->faker->dateTimeBetween($startDate, $startDate->copy()->addMonth()));

        return [
            'title' => $this->faker->sentence,
            'slug' => $this->faker->slug,
            'user_id' => User::factory(),
            'venue_id' => Venue::factory(),
            'category_id' => Category::factory(),
            'tag_id' => Tag::factory(),
            'description' => $this->faker->paragraph,
            'content' => json_encode([
                [
                    'type' => 'Add Content',
                    'data' => [
                        'description' => $this->faker->paragraphs(3, true),
                    ],
                ],
            ]),
            'event_date' => [
                [
                    'start_date' => $startDate->format('d/m/Y H:i'),
                    'end_date' => $endDate->format('d/m/Y H:i'),
                ],
            ],
            //            'is_sell_tickets' => $this->faker->boolean,
            'feature_image' => $this->faker->imageUrl(),
            'action_content' => json_encode([
                [
                    'type' => 'Link_Button',
                    'data' => [
                        'label' => 'Register Now',
                        'url' => $this->faker->url,
                    ],
                ],
            ]),
        ];
    }
}
