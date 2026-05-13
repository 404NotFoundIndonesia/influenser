<?php

use App\Enum\Platform;
use App\Models\Influencer;
use App\Models\KeyOpinionLeader;
use App\Services\ThirdParty\ApifyKolSyncService;
use App\Services\ThirdParty\ApifyServiceInterface;

$fixture = [['fans' => 500_000, 'heart' => 1_000_000, 'video' => 200, 'engagementRate' => 6.5]];

test('sync returns array with followers key for tiktok', function () use ($fixture) {
    config(['influenser.apify.actors.tiktok' => 'actor~tiktok']);

    $mock = Mockery::mock(ApifyServiceInterface::class);
    $mock->shouldReceive('runActor')->andReturn($fixture);

    $influencer = Influencer::factory()->create();
    $kol = KeyOpinionLeader::factory()->create([
        'influencer_id' => $influencer->id,
        'platform'      => Platform::TikTok->value,
        'username'      => 'testuser',
    ]);

    $result = (new ApifyKolSyncService($mock))->sync($kol);

    expect($result)->toHaveKey('followers')
        ->and($result['followers'])->toBe(500_000);
});

test('sync returns array with followers key for instagram', function () {
    config(['influenser.apify.actors.instagram' => 'actor~instagram']);

    $mock = Mockery::mock(ApifyServiceInterface::class);
    $mock->shouldReceive('runActor')->andReturn([['followersCount' => 80_000, 'engagementRate' => 3.2]]);

    $influencer = Influencer::factory()->create();
    $kol = KeyOpinionLeader::factory()->create([
        'influencer_id' => $influencer->id,
        'platform'      => Platform::Instagram->value,
        'username'      => 'instauser',
    ]);

    $result = (new ApifyKolSyncService($mock))->sync($kol);

    expect($result)->toHaveKey('followers')
        ->and($result['followers'])->toBe(80_000);
});

test('sync returns array with followers key for youtube', function () {
    config(['influenser.apify.actors.youtube' => 'actor~youtube']);

    $mock = Mockery::mock(ApifyServiceInterface::class);
    $mock->shouldReceive('runActor')->andReturn([['numberOfSubscribers' => 1_200_000]]);

    $influencer = Influencer::factory()->create();
    $kol = KeyOpinionLeader::factory()->create([
        'influencer_id' => $influencer->id,
        'platform'      => Platform::Youtube->value,
        'username'      => 'ytuser',
    ]);

    $result = (new ApifyKolSyncService($mock))->sync($kol);

    expect($result)->toHaveKey('followers')
        ->and($result['followers'])->toBe(1_200_000);
});

test('sync returns array with followers key for facebook', function () {
    config(['influenser.apify.actors.facebook' => 'actor~facebook']);

    $mock = Mockery::mock(ApifyServiceInterface::class);
    $mock->shouldReceive('runActor')->andReturn([['followers' => 300_000]]);

    $influencer = Influencer::factory()->create();
    $kol = KeyOpinionLeader::factory()->create([
        'influencer_id' => $influencer->id,
        'platform'      => Platform::Facebook->value,
        'username'      => 'fbuser',
    ]);

    $result = (new ApifyKolSyncService($mock))->sync($kol);

    expect($result)->toHaveKey('followers')
        ->and($result['followers'])->toBe(300_000);
});

test('sync throws RuntimeException for unsupported platform', function () {
    $mock = Mockery::mock(ApifyServiceInterface::class);

    $influencer = Influencer::factory()->create();
    $kol = KeyOpinionLeader::factory()->create([
        'influencer_id' => $influencer->id,
        'platform'      => Platform::LinkedIn->value,
        'username'      => 'linkedinuser',
    ]);

    expect(fn () => (new ApifyKolSyncService($mock))->sync($kol))
        ->toThrow(\RuntimeException::class);
});
