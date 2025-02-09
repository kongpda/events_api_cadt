<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
final class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'ticket_type_id' => TicketType::factory(),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled']),
            'purchase_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'price' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }
}
