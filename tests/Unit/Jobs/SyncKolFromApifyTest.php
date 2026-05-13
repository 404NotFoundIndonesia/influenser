<?php

use App\Enum\Platform;
use App\Jobs\SyncKolFromApify;
use App\Jobs\SyncKolFromCreatorDB;
use App\Models\Influencer;
use App\Models\KeyOpinionLeader;
use App\Services\ThirdParty\ApifyKolSyncService;
use Illuminate\Support\Facades\Queue;

test('handle updates kol metrics synced_at and clears syncing_at on success', function () {
    $influencer = Influencer::factory()->create();
    $kol = KeyOpinionLeader::factory()->create([
        'influencer_id'  => $influencer->id,
        'platform'       => Platform::TikTok->value,
        'username'       => 'testuser',
        'followers'      => 1_000,
        'engagement_rate' => 1.0,
    ]);

    $mock = Mockery::mock(ApifyKolSyncService::class);
    $mock->shouldReceive('sync')->with($kol)->andReturn([
        'followers'       => 750_000,
        'engagement_rate' => 5.8,
        'following'       => 300,
        'total_content'   => 150,
        'likes'           => 0,
        'views'           => 0,
        'avg_views'       => 0,
        'avg_likes'       => 0,
        'avg_shares'      => 0,
        'avg_comments'    => 0,
    ]);

    (new SyncKolFromApify($kol))->handle($mock);

    $kol->refresh();
    expect($kol->followers)->toBe(750_000)
        ->and($kol->synced_at)->not->toBeNull()
        ->and($kol->syncing_at)->toBeNull();
});

test('handle clears syncing_at and dispatches SyncKolFromCreatorDB fallback when CREATOR_DB_API is set', function () {
    Queue::fake();
    config(['influenser.creator_db.key' => 'some-api-key']);

    $influencer = Influencer::factory()->create();
    $kol = KeyOpinionLeader::factory()->create([
        'influencer_id' => $influencer->id,
        'platform'      => Platform::TikTok->value,
    ]);

    $mock = Mockery::mock(ApifyKolSyncService::class);
    $mock->shouldReceive('sync')->andThrow(new \RuntimeException('Apify down'));

    (new SyncKolFromApify($kol))->handle($mock);

    $kol->refresh();
    expect($kol->syncing_at)->toBeNull();
    Queue::assertPushed(SyncKolFromCreatorDB::class, fn ($job) => $job->kol->id === $kol->id);
});

test('handle clears syncing_at and does not dispatch fallback when CREATOR_DB_API is not set', function () {
    Queue::fake();
    config(['influenser.creator_db.key' => null]);

    $influencer = Influencer::factory()->create();
    $kol = KeyOpinionLeader::factory()->create([
        'influencer_id' => $influencer->id,
        'platform'      => Platform::TikTok->value,
    ]);

    $mock = Mockery::mock(ApifyKolSyncService::class);
    $mock->shouldReceive('sync')->andThrow(new \RuntimeException('Apify down'));

    (new SyncKolFromApify($kol))->handle($mock);

    $kol->refresh();
    expect($kol->syncing_at)->toBeNull();
    Queue::assertNothingPushed();
});
