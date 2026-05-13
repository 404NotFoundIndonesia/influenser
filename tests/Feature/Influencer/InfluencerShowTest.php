<?php

use App\Models\Influencer;
use App\Models\KeyOpinionLeader;
use App\Models\User;

// ── T6.5 — influencer.show includes syncing_at on each KOL ───────────────────

test('influencer show includes syncing_at on each kol', function () {
    $user = User::factory()->create();
    $influencer = Influencer::factory()->create();
    KeyOpinionLeader::factory()->create([
        'influencer_id' => $influencer->id,
        'syncing_at' => null,
    ]);

    $this->actingAs($user)
        ->withoutVite()
        ->get(route('influencer.show', $influencer))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('influencer/Show')
            ->has('item.key_opinion_leaders', 1)
            ->has('item.key_opinion_leaders.0.syncing_at')
        );
});

test('influencer show kol syncing_at reflects current value', function () {
    $user = User::factory()->create();
    $influencer = Influencer::factory()->create();
    KeyOpinionLeader::factory()->create([
        'influencer_id' => $influencer->id,
        'syncing_at' => now(),
    ]);

    $this->actingAs($user)
        ->withoutVite()
        ->get(route('influencer.show', $influencer))
        ->assertInertia(fn ($page) => $page
            ->where('item.key_opinion_leaders.0.is_syncing', true)
        );
});
