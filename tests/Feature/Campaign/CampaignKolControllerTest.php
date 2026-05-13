<?php

use App\Models\Campaign;
use App\Models\Influencer;
use App\Models\KeyOpinionLeader;
use App\Models\User;

// ── Auth guard ────────────────────────────────────────────────────────────────

test('guests cannot attach a kol to a campaign', function () {
    $campaign = Campaign::factory()->create();
    $kol = KeyOpinionLeader::factory()->create();

    $this->post(route('campaign.kol.store', $campaign), [
        'key_opinion_leader_id' => $kol->id,
    ])->assertRedirect(route('login'));
});

test('guests cannot detach a kol from a campaign', function () {
    $campaign = Campaign::factory()->create();
    $kol = KeyOpinionLeader::factory()->create();
    $campaign->keyOpinionLeaders()->attach($kol->id);

    $this->delete(route('campaign.kol.destroy', [$campaign, $kol]))
        ->assertRedirect(route('login'));
});

// ── Store (attach) ────────────────────────────────────────────────────────────

test('authenticated user can attach a kol to a campaign', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $kol = KeyOpinionLeader::factory()->create();

    $this->actingAs($user)
        ->post(route('campaign.kol.store', $campaign), [
            'key_opinion_leader_id' => $kol->id,
            'deliverable' => '3 Instagram posts',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('campaign_key_opinion_leader', [
        'campaign_id'           => $campaign->id,
        'key_opinion_leader_id' => $kol->id,
        'deliverable'           => '3 Instagram posts',
    ]);
});

test('attaching the same kol twice does not create a duplicate pivot row', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $kol = KeyOpinionLeader::factory()->create();

    $this->actingAs($user)
        ->post(route('campaign.kol.store', $campaign), ['key_opinion_leader_id' => $kol->id]);

    $this->actingAs($user)
        ->post(route('campaign.kol.store', $campaign), ['key_opinion_leader_id' => $kol->id]);

    expect($campaign->keyOpinionLeaders()->count())->toBe(1);
});

test('store returns 422 when key_opinion_leader_id is missing', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();

    $this->actingAs($user)
        ->post(route('campaign.kol.store', $campaign), [])
        ->assertSessionHasErrors('key_opinion_leader_id');
});

test('store returns 422 when key_opinion_leader_id does not exist', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();

    $this->actingAs($user)
        ->post(route('campaign.kol.store', $campaign), [
            'key_opinion_leader_id' => '00000000-0000-0000-0000-000000000000',
        ])
        ->assertSessionHasErrors('key_opinion_leader_id');
});

// ── Destroy (detach) ──────────────────────────────────────────────────────────

test('authenticated user can detach a kol from a campaign', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $kol = KeyOpinionLeader::factory()->create();
    $campaign->keyOpinionLeaders()->attach($kol->id);

    $this->actingAs($user)
        ->delete(route('campaign.kol.destroy', [$campaign, $kol]))
        ->assertRedirect();

    $this->assertDatabaseMissing('campaign_key_opinion_leader', [
        'campaign_id'           => $campaign->id,
        'key_opinion_leader_id' => $kol->id,
    ]);
});

test('detaching a kol does not delete the kol record itself', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $kol = KeyOpinionLeader::factory()->create();
    $campaign->keyOpinionLeaders()->attach($kol->id);

    $this->actingAs($user)
        ->delete(route('campaign.kol.destroy', [$campaign, $kol]));

    $this->assertDatabaseHas('key_opinion_leaders', ['id' => $kol->id]);
});

// ── Campaign show props ───────────────────────────────────────────────────────

test('campaign show page includes key_opinion_leaders prop', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $kol = KeyOpinionLeader::factory()->create();
    $campaign->keyOpinionLeaders()->attach($kol->id, ['deliverable' => 'test deliverable']);

    $this->actingAs($user)
        ->withoutVite()
        ->get(route('campaign.show', $campaign))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('campaign/Show')
            ->has('item.key_opinion_leaders', 1)
            ->where('item.key_opinion_leaders.0.id', $kol->id)
        );
});

test('campaign show page includes influencers prop', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    Influencer::factory()->count(3)->create();

    $this->actingAs($user)
        ->withoutVite()
        ->get(route('campaign.show', $campaign))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('campaign/Show')
            ->has('influencers', 3)
        );
});
