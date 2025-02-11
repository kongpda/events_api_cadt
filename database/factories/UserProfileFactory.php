<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserProfile>
 */
final class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'birth_date' => fake()->date(),
            'phone' => fake()->phoneNumber(),
            'avatar' => fake()->imageUrl(),
            'status' => fake()->randomElement(['active', 'inactive', 'pending']),
            'bio' => fake()->paragraph(),
            'address' => fake()->address(),
            'social_links' => [
                'twitter' => fake()->url(),
                'facebook' => fake()->url(),
                'linkedin' => fake()->url(),
            ],
        ];
    }
}
