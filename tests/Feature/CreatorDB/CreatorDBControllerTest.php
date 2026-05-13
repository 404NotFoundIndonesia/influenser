<?php

use App\Enum\Platform;
use App\Jobs\SyncKolFromCreatorDB;
use App\Models\Influencer;
use App\Models\KeyOpinionLeader;
use App\Models\User;
use App\Services\ThirdParty\CreatorDBServiceInterface;
use Illuminate\Support\Facades\Queue;

// ── T6.1 — Search ─────────────────────────────────────────────────────────────

test('search returns 200 with normalized data for valid platform and username', function () {
    $user = User::factory()->create();

    $mock = Mockery::mock(CreatorDBServiceInterface::class);
    $mock->shouldReceive('tiktokBasic')
        ->with('testuser')
        ->andReturn([
            'followers'   => 100_000,
            'following'   => 500,
            'engageRate'  => 4.5,
            'avgPlays'    => 50_000,
        ]);
    app()->instance(CreatorDBServiceInterface::class, $mock);

    $this->actingAs($user)
        ->getJson(route('creator-db.search', ['platform' => Platform::TikTok->value, 'username' => 'testuser']))
        ->assertOk()
        ->assertJsonFragment(['followers' => 100_000])
        ->assertJsonFragment(['engagement_rate' => 4.5]);
});

test('search returns 422 when username is missing', function () {
    $user = User::factory()->create();

    app()->instance(CreatorDBServiceInterface::class, Mockery::mock(CreatorDBServiceInterface::class));

    $this->actingAs($user)
        ->getJson(route('creator-db.search', ['platform' => Platform::TikTok->value]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('username');
});

test('search returns 422 when platform is invalid', function () {
    $user = User::factory()->create();

    app()->instance(CreatorDBServiceInterface::class, Mockery::mock(CreatorDBServiceInterface::class));

    $this->actingAs($user)
        ->getJson(route('creator-db.search', ['platform' => 'invalid-platform', 'username' => 'testuser']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('platform');
});

test('search returns 502 when service throws exception', function () {
    $user = User::factory()->create();

    $mock = Mockery::mock(CreatorDBServiceInterface::class);
    $mock->shouldReceive('tiktokBasic')->andThrow(new \RuntimeException('API down'));
    app()->instance(CreatorDBServiceInterface::class, $mock);

    $this->actingAs($user)
        ->getJson(route('creator-db.search', ['platform' => Platform::TikTok->value, 'username' => 'testuser']))
        ->assertStatus(502);
});

test('guests cannot access search endpoint', function () {
    $this->getJson(route('creator-db.search', ['platform' => Platform::TikTok->value, 'username' => 'testuser']))
        ->assertUnauthorized();
});

// ── T6.2 — Import ─────────────────────────────────────────────────────────────

test('import creates kol record with correct platform and synced_at', function () {
    $user       = User::factory()->create();
    $influencer = Influencer::factory()->create();

    $mock = Mockery::mock(CreatorDBServiceInterface::class);
    $mock->shouldReceive('instagramBasic')
        ->with('instauser')
        ->andReturn([
            'followers'  => 50_000,
            'following'  => 300,
            'engageRate' => 3.2,
            'avgLikes'   => 1500,
        ]);
    app()->instance(CreatorDBServiceInterface::class, $mock);

    $this->actingAs($user)
        ->post(route('influencer.kol.import', $influencer), [
            'platform' => Platform::Instagram->value,
            'username' => 'instauser',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('key_opinion_leaders', [
        'influencer_id'  => $influencer->id,
        'platform'       => Platform::Instagram->value,
        'username'       => 'instauser',
        'followers'      => 50_000,
    ]);

    $kol = KeyOpinionLeader::where('influencer_id', $influencer->id)->first();
    expect($kol->synced_at)->not->toBeNull();
});

test('import does not create kol when service throws exception', function () {
    $user       = User::factory()->create();
    $influencer = Influencer::factory()->create();

    $mock = Mockery::mock(CreatorDBServiceInterface::class);
    $mock->shouldReceive('tiktokBasic')->andThrow(new \RuntimeException('API down'));
    app()->instance(CreatorDBServiceInterface::class, $mock);

    $this->actingAs($user)
        ->post(route('influencer.kol.import', $influencer), [
            'platform' => Platform::TikTok->value,
            'username' => 'testuser',
        ]);

    $this->assertDatabaseEmpty('key_opinion_leaders');
});

test('guests cannot import kol', function () {
    $influencer = Influencer::factory()->create();

    $this->post(route('influencer.kol.import', $influencer), [
        'platform' => Platform::TikTok->value,
        'username' => 'testuser',
    ])->assertRedirect(route('login'));
});

// ── T6.3 — Sync route ─────────────────────────────────────────────────────────

test('sync route dispatches SyncKolFromCreatorDB job', function () {
    Queue::fake();

    $user       = User::factory()->create();
    $influencer = Influencer::factory()->create();
    $kol        = KeyOpinionLeader::factory()->create(['influencer_id' => $influencer->id]);

    app()->instance(CreatorDBServiceInterface::class, Mockery::mock(CreatorDBServiceInterface::class));

    $this->actingAs($user)
        ->post(route('influencer.kol.sync.creator-db', [$influencer, $kol]))
        ->assertRedirect();

    Queue::assertPushed(SyncKolFromCreatorDB::class, fn ($job) => $job->kol->id === $kol->id);
});

test('guests cannot trigger sync', function () {
    $influencer = Influencer::factory()->create();
    $kol        = KeyOpinionLeader::factory()->create(['influencer_id' => $influencer->id]);

    app()->instance(CreatorDBServiceInterface::class, Mockery::mock(CreatorDBServiceInterface::class));

    $this->post(route('influencer.kol.sync.creator-db', [$influencer, $kol]))
        ->assertRedirect(route('login'));
});
