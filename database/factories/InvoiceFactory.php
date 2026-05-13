<?php

namespace Database\Factories;

use App\Enum\InvoiceStatus;
use App\Models\Campaign;
use App\Models\Influencer;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'influencer_id' => Influencer::factory(),
            'key_opinion_leader_id' => null,
            'amount' => $this->faker->numberBetween(500_000, 10_000_000),
            'status' => InvoiceStatus::Unpaid,
            'paid_at' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => InvoiceStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => InvoiceStatus::Pending]);
    }
}
