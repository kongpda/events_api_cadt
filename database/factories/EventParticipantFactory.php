<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ParticipationStatus;
use App\Enums\ParticipationType;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventParticipant>
 */
final class EventParticipantFactory extends Factory
{
    protected $model = EventParticipant::class;

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
            'status' => $this->faker->randomElement(ParticipationStatus::cases()),
            'participation_type' => $this->faker->randomElement(ParticipationType::cases()),
            'ticket_id' => Ticket::factory(),
            'check_in_time' => $this->faker->optional()->dateTime(),
            'joined_at' => $this->faker->dateTime(),
        ];
    }
}
