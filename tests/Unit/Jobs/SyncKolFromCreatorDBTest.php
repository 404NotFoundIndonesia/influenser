<?php

use App\Enum\Platform;
use App\Jobs\SyncKolFromCreatorDB;
use App\Models\Influencer;
use App\Models\KeyOpinionLeader;
use App\Services\ThirdParty\CreatorDBServiceInterface;
use Illuminate\Support\Facades\Log;

test('handle updates kol metrics synced_at and clears syncing_at on success', function () {
    $influencer = Influencer::factory()->create();
    $kol = KeyOpinionLeader::factory()->create([
        'influencer_id' => $influencer->id,
        'platform' => Platform::TikTok->value,
        'username' => 'testuser',
        'followers' => 1_000,
        'engagement_rate' => 1.0,
    ]);

    $mock = Mockery::mock(CreatorDBServiceInterface::class);
    $mock->shouldReceive('tiktokBasic')
        ->with('testuser')
        ->andReturn([
            'followers' => 250_000,
            'engageRate' => 5.5,
            'avgPlays' => 80_000,
        ]);

    (new SyncKolFromCreatorDB($kol))->handle($mock);

    $kol->refresh();
    expect($kol->followers)->toBe(250_000)
        ->and($kol->synced_at)->not->toBeNull()
        ->and($kol->syncing_at)->toBeNull();
});

test('handle clears syncing_at and logs error when service throws', function () {
    Log::spy();

    $influencer = Influencer::factory()->create();
    $kol = KeyOpinionLeader::factory()->create([
        'influencer_id' => $influencer->id,
        'platform' => Platform::TikTok->value,
        'username' => 'testuser',
    ]);

    $mock = Mockery::mock(CreatorDBServiceInterface::class);
    $mock->shouldReceive('tiktokBasic')->andThrow(new \RuntimeException('API down'));

    (new SyncKolFromCreatorDB($kol))->handle($mock);

    $kol->refresh();
    expect($kol->syncing_at)->toBeNull();

    Log::shouldHaveReceived('error')->once();
});

test('handle updates instagram kol metrics correctly', function () {
    $influencer = Influencer::factory()->create();
    $kol = KeyOpinionLeader::factory()->create([
        'influencer_id' => $influencer->id,
        'platform' => Platform::Instagram->value,
        'username' => 'instauser',
        'followers' => 500,
    ]);

    $mock = Mockery::mock(CreatorDBServiceInterface::class);
    $mock->shouldReceive('instagramBasic')
        ->with('instauser')
        ->andReturn([
            'followers' => 120_000,
            'engageRate' => 2.8,
        ]);

    (new SyncKolFromCreatorDB($kol))->handle($mock);

    $kol->refresh();
    expect($kol->followers)->toBe(120_000)
        ->and($kol->synced_at)->not->toBeNull()
        ->and($kol->syncing_at)->toBeNull();
});

test('handle updates youtube kol mapping subscribers to followers', function () {
    $influencer = Influencer::factory()->create();
    $kol = KeyOpinionLeader::factory()->create([
        'influencer_id' => $influencer->id,
        'platform' => Platform::Youtube->value,
        'username' => 'ytuser',
        'followers' => 100,
    ]);

    $mock = Mockery::mock(CreatorDBServiceInterface::class);
    $mock->shouldReceive('youtubeBasic')
        ->with('ytuser')
        ->andReturn([
            'subscribers' => 500_000,
            'engageRate1Y' => 3.0,
        ]);

    (new SyncKolFromCreatorDB($kol))->handle($mock);

    $kol->refresh();
    expect($kol->followers)->toBe(500_000)
        ->and($kol->syncing_at)->toBeNull();
});
