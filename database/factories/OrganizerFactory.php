<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organizer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

final class OrganizerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Organizer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'email' => fake()->boolean(80) ? fake()->unique()->companyEmail() : null,
            'phone' => fake()->phoneNumber(),
            'description' => fake()->paragraph(),
            'address' => fake()->address(),
            'website' => fake()->url(),
            'social_media' => fake()->url(),
            'logo' => fake()->imageUrl(),
            'is_verified' => fake()->boolean(20),
        ];
    }
}
