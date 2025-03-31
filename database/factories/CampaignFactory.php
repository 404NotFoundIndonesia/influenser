<?php

namespace Database\Factories;

use App\Enum\CampaignStatus;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'start_date' => $this->faker->dateTimeBetween('-1 years', 'now'),
            'end_date' => $this->faker->dateTimeBetween('now', '+2 years'),
            'status' => $this->faker->randomElement(CampaignStatus::cases()),
        ];
    }
}
