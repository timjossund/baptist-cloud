<?php

namespace Database\Factories;

use App\Models\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'position' => 'Pastor',
            'church' => 'First Baptist',
            'city' => fake()->city(),
            'state' => 'TX',
            'content' => fake()->paragraph(),
            'email' => fake()->safeEmail(),
            'phone' => '555-1234',
            'approved_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['approved_at' => null]);
    }
}
