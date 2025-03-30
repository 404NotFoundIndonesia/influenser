<?php

namespace Database\Seeders;

use App\Models\Influencer;
use App\Models\Niche;
use Illuminate\Database\Seeder;
use Random\RandomException;

class NicheSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @throws RandomException
     */
    public function run(): void
    {
        $niches = Niche::factory()->count(25)->create();

        $influencers = Influencer::all();
        foreach ($influencers as $influencer) {
            $count = random_int(1, 3);
            $influencer->niches()->sync($niches->shuffle()->take($count));
        }
    }
}
