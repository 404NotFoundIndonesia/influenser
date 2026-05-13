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

// ── Update (engagement metrics) ───────────────────────────────────────────────

test('guests cannot update engagement metrics', function () {
    $campaign = Campaign::factory()->create();
    $kol = KeyOpinionLeader::factory()->create();
    $campaign->keyOpinionLeaders()->attach($kol->id);

    $this->put(route('campaign.kol.update', [$campaign, $kol]), [
        'actual_views' => 1000,
    ])->assertRedirect(route('login'));
});

test('authenticated user can update engagement metrics on attached kol', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $kol = KeyOpinionLeader::factory()->create();
    $campaign->keyOpinionLeaders()->attach($kol->id);

    $this->actingAs($user)
        ->put(route('campaign.kol.update', [$campaign, $kol]), [
            'actual_views'    => 5000,
            'actual_likes'    => 200,
            'actual_comments' => 50,
            'actual_shares'   => 30,
            'posted_at'       => '2026-01-15',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('campaign_key_opinion_leader', [
        'campaign_id'           => $campaign->id,
        'key_opinion_leader_id' => $kol->id,
        'actual_views'          => 5000,
        'actual_likes'          => 200,
        'actual_comments'       => 50,
        'actual_shares'         => 30,
    ]);
});

test('updating with only posted_at leaves numeric metrics null', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $kol = KeyOpinionLeader::factory()->create();
    $campaign->keyOpinionLeaders()->attach($kol->id);

    $this->actingAs($user)
        ->put(route('campaign.kol.update', [$campaign, $kol]), [
            'posted_at' => '2026-02-10',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('campaign_key_opinion_leader', [
        'campaign_id'           => $campaign->id,
        'key_opinion_leader_id' => $kol->id,
        'actual_views'          => null,
        'actual_likes'          => null,
        'actual_comments'       => null,
        'actual_shares'         => null,
    ]);
});

test('updating engagement on non-attached kol returns 404', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $kol = KeyOpinionLeader::factory()->create();

    $this->actingAs($user)
        ->put(route('campaign.kol.update', [$campaign, $kol]), [
            'actual_views' => 100,
        ])
        ->assertNotFound();
});

test('update returns 422 when actual_views is not an integer', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $kol = KeyOpinionLeader::factory()->create();
    $campaign->keyOpinionLeaders()->attach($kol->id);

    $this->actingAs($user)
        ->put(route('campaign.kol.update', [$campaign, $kol]), [
            'actual_views' => 'not-a-number',
        ])
        ->assertSessionHasErrors('actual_views');
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

test('updated engagement metrics are visible in campaign show props', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $kol = KeyOpinionLeader::factory()->create();
    $campaign->keyOpinionLeaders()->attach($kol->id, [
        'actual_views'    => 8000,
        'actual_likes'    => 400,
        'actual_comments' => 75,
        'actual_shares'   => 20,
        'posted_at'       => '2026-03-01',
    ]);

    $this->actingAs($user)
        ->withoutVite()
        ->get(route('campaign.show', $campaign))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('campaign/Show')
            ->where('item.key_opinion_leaders.0.pivot.actual_views', 8000)
            ->where('item.key_opinion_leaders.0.pivot.actual_likes', 400)
            ->where('item.key_opinion_leaders.0.pivot.actual_comments', 75)
            ->where('item.key_opinion_leaders.0.pivot.actual_shares', 20)
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
