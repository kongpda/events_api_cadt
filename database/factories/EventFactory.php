<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
final class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(3);
        $eventTypes = ['in_person', 'online', 'hybrid'];
        $selectedType = $this->faker->randomElement($eventTypes);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $this->faker->paragraphs(3, true),
            'address' => $this->faker->address(),
            'feature_image' => $this->faker->imageUrl(),
            'start_date' => $this->faker->dateTimeBetween('now', '+2 months'),
            'end_date' => $this->faker->dateTimeBetween('+2 months', '+4 months'),
            'category_id' => Category::factory(),
            'user_id' => User::factory(),
            'organizer_id' => Organizer::factory(),
            'participation_type' => $this->faker->randomElement(['paid', 'free']),
            'capacity' => $this->faker->numberBetween(0, 1000),
            'registration_deadline' => $this->faker->dateTimeBetween('now', '+1 month'),
            'registration_status' => $this->faker->randomElement(['open', 'closed', 'full']),
            'event_type' => $selectedType,
            'online_url' => in_array($selectedType, ['online', 'hybrid']) ? $this->faker->url() : null,
        ];
    }
}
