<?php

namespace Database\Factories;

use App\Models\Niche;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Niche>
 */
class NicheFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->paragraph(),
            'active' => $this->faker->boolean(90),
        ];
    }
}
