<?php

namespace Database\Factories;

use App\Enum\Platform;
use App\Models\Influencer;
use App\Models\KeyOpinionLeader;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KeyOpinionLeader>
 */
class KeyOpinionLeaderFactory extends Factory
{
    public function definition(): array
    {
        $platform = $this->faker->randomElement(Platform::cases());
        $username = $this->faker->userName();

        return [
            'influencer_id' => Influencer::factory(),
            'username' => $username,
            'platform' => $platform->value,
            'link' => $platform->profileUrl($username),
            'bio' => $this->faker->optional()->sentence(),
            'engagement_rate' => $this->faker->randomFloat(2, 0.5, 15),
            'followers' => $this->faker->numberBetween(1_000, 5_000_000),
            'following' => $this->faker->numberBetween(100, 5_000),
            'total_content' => $this->faker->numberBetween(10, 1_000),
            'views' => $this->faker->numberBetween(10_000, 100_000_000),
            'likes' => $this->faker->numberBetween(5_000, 50_000_000),
            'shares' => $this->faker->numberBetween(100, 1_000_000),
            'comments' => $this->faker->numberBetween(100, 500_000),
            'avg_views' => $this->faker->numberBetween(1_000, 500_000),
            'avg_likes' => $this->faker->numberBetween(500, 200_000),
            'avg_shares' => $this->faker->numberBetween(10, 10_000),
            'avg_comments' => $this->faker->numberBetween(10, 5_000),
            'endorsement_rate' => $this->faker->numberBetween(500_000, 50_000_000),
        ];
    }
}
