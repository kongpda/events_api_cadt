<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Music',
            'Theatre',
            'Dance',
            'Comedy',
            'Opera',
            'Magic Show',
            'Circus',
        ];

        foreach ($categories as $position => $category) {
            Category::factory()->create([
                'name' => $category,
                'slug' => Str::slug($category),
                'description' => fake()->paragraph(),
                'is_active' => true,
                'position' => $position,
            ]);
        }
    }
}
