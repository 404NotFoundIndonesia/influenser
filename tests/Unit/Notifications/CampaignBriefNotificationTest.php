<?php

use App\Models\Campaign;
use App\Models\Influencer;
use App\Models\KeyOpinionLeader;
use App\Notifications\CampaignBriefNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification;

// ── via() ─────────────────────────────────────────────────────────────────────

test('campaign brief notification is sent via mail', function () {
    $campaign = new Campaign(['name' => 'Test Campaign']);
    $kol      = new KeyOpinionLeader(['platform' => 'tiktok', 'username' => 'testuser']);

    $notification = new CampaignBriefNotification($campaign, $kol);

    expect($notification->via(new Influencer()))->toBe(['mail']);
});

// ── toMail() ──────────────────────────────────────────────────────────────────

test('campaign brief notification has correct subject', function () {
    $campaign = new Campaign(['name' => 'Summer Promo 2026']);
    $kol      = new KeyOpinionLeader(['platform' => 'instagram', 'username' => 'creator']);

    $mail = (new CampaignBriefNotification($campaign, $kol))
        ->toMail(new Influencer(['name' => 'Jane Doe', 'email' => 'jane@example.com']));

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe('Campaign Brief: Summer Promo 2026');
});

test('campaign brief notification mail contains campaign name in view data', function () {
    $campaign = new Campaign(['name' => 'Brand Collab']);
    $kol      = new KeyOpinionLeader(['platform' => 'tiktok', 'username' => 'creator99']);

    $mail = (new CampaignBriefNotification($campaign, $kol))
        ->toMail(new Influencer(['name' => 'John', 'email' => 'john@example.com']));

    expect($mail->viewData['campaign']->name)->toBe('Brand Collab');
});

// ── Notification::assertSentTo ────────────────────────────────────────────────

test('campaign brief notification targets the influencer email', function () {
    Notification::fake();

    $influencer = Influencer::factory()->create(['email' => 'kol@test.com']);
    $campaign   = Campaign::factory()->create();
    $kol        = KeyOpinionLeader::factory()->create(['influencer_id' => $influencer->id]);

    $influencer->notify(new CampaignBriefNotification($campaign, $kol));

    Notification::assertSentTo(
        $influencer,
        CampaignBriefNotification::class,
        fn ($notification) => $notification->campaign->is($campaign),
    );
});
