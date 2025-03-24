<?php

namespace Database\Seeders;

use App\Models\Influencer;
use Illuminate\Database\Seeder;

class InfluencerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Influencer::factory()->count(100)->create();
    }
}
