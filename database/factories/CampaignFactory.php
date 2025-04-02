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
        $start = $this->faker->boolean()
            ? $this->faker->dateTimeBetween('-1 years', 'now')
            : null;
        $end = $this->faker->boolean()
            ? $this->faker->dateTimeBetween('now', '+2 years')
            : null;

        return [
            'name' => $this->faker->sentence(),
            'description' => $this->faker->text(),
            'start_date' => $start,
            'end_date' => $end,
            'status' => $this->faker->randomElement(CampaignStatus::cases()),
        ];
    }
}
