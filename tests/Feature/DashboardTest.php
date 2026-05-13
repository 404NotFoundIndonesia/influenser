<?php

use App\Enum\CampaignStatus;
use App\Enum\InvoiceStatus;
use App\Models\Campaign;
use App\Models\Influencer;
use App\Models\Invoice;
use App\Models\KeyOpinionLeader;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->withoutVite()->get('/dashboard')->assertStatus(200);
});

// ── T5.1 — Analytics props ────────────────────────────────────────────────────

test('dashboard returns correct totalInfluencers prop', function () {
    $user = User::factory()->create();
    Influencer::factory()->count(3)->create();

    $this->actingAs($user)
        ->withoutVite()
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page->where('totalInfluencers', 3));
});

test('dashboard returns correct totalCampaigns and activeCampaigns props', function () {
    $user = User::factory()->create();
    Campaign::factory()->count(2)->create(['status' => CampaignStatus::Ongoing]);
    Campaign::factory()->create(['status' => CampaignStatus::Draft]);

    $this->actingAs($user)
        ->withoutVite()
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('totalCampaigns', 3)
            ->where('activeCampaigns', 2)
        );
});

test('dashboard returns correct totalInvoiced and totalPaid props', function () {
    $user     = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $influencer = Influencer::factory()->create();

    Invoice::factory()->create([
        'campaign_id'   => $campaign->id,
        'influencer_id' => $influencer->id,
        'amount'        => 100_000,
        'status'        => InvoiceStatus::Paid,
    ]);
    Invoice::factory()->create([
        'campaign_id'   => $campaign->id,
        'influencer_id' => $influencer->id,
        'amount'        => 50_000,
        'status'        => InvoiceStatus::Unpaid,
    ]);

    $this->actingAs($user)
        ->withoutVite()
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('totalInvoiced', 150_000)
            ->where('totalPaid', 100_000)
        );
});

test('dashboard topInfluencers contains at most 5 entries sorted by engagement rate', function () {
    $user = User::factory()->create();

    for ($i = 1; $i <= 6; $i++) {
        $influencer = Influencer::factory()->create();
        KeyOpinionLeader::factory()->create([
            'influencer_id'  => $influencer->id,
            'engagement_rate' => $i * 10,
        ]);
    }

    $this->actingAs($user)
        ->withoutVite()
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->has('topInfluencers', 5)
            ->where('topInfluencers.0.avg_engagement', 60)
        );
});

test('dashboard campaignStatusBreakdown has all CampaignStatus keys', function () {
    $user = User::factory()->create();
    Campaign::factory()->create(['status' => CampaignStatus::Ongoing]);

    $this->actingAs($user)
        ->withoutVite()
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->has('campaignStatusBreakdown.draft')
            ->has('campaignStatusBreakdown.ongoing')
            ->has('campaignStatusBreakdown.completed')
            ->has('campaignStatusBreakdown.cancelled')
            ->where('campaignStatusBreakdown.ongoing', 1)
        );
});
