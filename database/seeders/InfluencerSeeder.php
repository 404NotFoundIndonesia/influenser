<?php

namespace Database\Seeders;

use App\Enum\Platform;
use App\Models\Influencer;
use Illuminate\Database\Seeder;

class InfluencerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platforms = Platform::cases();
        $influencers = Influencer::factory()->count(1_000)->create();

        /** @var Influencer $influencer */
        foreach ($influencers as $influencer) {
            foreach ($platforms as $platform) {
                if (fake()->boolean())
                    continue;

                $username = fake()->unique()->userName();

                $influencer->key_opinion_leaders()->create([
                    'username' => $username,
                    'platform' => $platform,
                    'link' => $platform->profileUrl($username),
                    'bio' => fake()->text(),
                    'engagement_rate' => fake()->randomFloat(2, 0, 100),
                    'followers' => fake()->numberBetween(0, 1000_000),
                    'following' => fake()->numberBetween(0, 1000_000),
                    'total_content' => fake()->numberBetween(0, 1000),
                    'views' => fake()->numberBetween(1000, 10_000_000),
                    'likes' => fake()->numberBetween(1000, 10_000_000),
                    'shares' => fake()->numberBetween(1000, 10_000_000),
                    'comments' => fake()->numberBetween(1000, 10_000_000),
                    'endorsement_rate' => fake()->numberBetween(100_000, 100_000_000),
                ]);
            }
        }
    }
}
