<?php

use App\Models\Campaign;
use App\Models\Influencer;
use App\Models\KeyOpinionLeader;

test('campaign has many key opinion leaders through pivot', function () {
    $campaign = Campaign::factory()->create();
    $kol = KeyOpinionLeader::factory()->create();

    $campaign->keyOpinionLeaders()->attach($kol->id, ['deliverable' => '2 TikTok videos']);

    $campaign->refresh();
    $loaded = $campaign->keyOpinionLeaders;

    expect($loaded)->toHaveCount(1);
    expect($loaded->first()->id)->toBe($kol->id);
    expect($loaded->first()->pivot->deliverable)->toBe('2 TikTok videos');
});

test('pivot exposes all extra columns', function () {
    $campaign = Campaign::factory()->create();
    $kol = KeyOpinionLeader::factory()->create();

    $campaign->keyOpinionLeaders()->attach($kol->id, [
        'deliverable'    => '1 reel',
        'posted_at'      => now()->toDateTimeString(),
        'actual_views'   => 50000,
        'actual_likes'   => 2000,
        'actual_comments' => 300,
        'actual_shares'  => 150,
    ]);

    $pivot = $campaign->keyOpinionLeaders()->first()->pivot;

    expect($pivot->deliverable)->toBe('1 reel');
    expect((int) $pivot->actual_views)->toBe(50000);
    expect((int) $pivot->actual_likes)->toBe(2000);
});

test('key opinion leader has many campaigns through pivot', function () {
    $campaign = Campaign::factory()->create();
    $kol = KeyOpinionLeader::factory()->create();

    $campaign->keyOpinionLeaders()->attach($kol->id);

    $kol->refresh();
    $loaded = $kol->campaigns;

    expect($loaded)->toHaveCount(1);
    expect($loaded->first()->id)->toBe($campaign->id);
});

test('detaching kol from campaign does not delete the kol record', function () {
    $campaign = Campaign::factory()->create();
    $kol = KeyOpinionLeader::factory()->create();

    $campaign->keyOpinionLeaders()->attach($kol->id);
    $campaign->keyOpinionLeaders()->detach($kol->id);

    expect($campaign->keyOpinionLeaders()->count())->toBe(0);
    expect(KeyOpinionLeader::find($kol->id))->not->toBeNull();
});
