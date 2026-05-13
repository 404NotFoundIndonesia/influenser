<?php

use App\Jobs\CampaignDeadlineReminderJob;
use App\Jobs\CampaignStartReminderJob;
use App\Models\Campaign;
use App\Models\User;
use App\Notifications\CampaignDeadlineReminderNotification;
use App\Notifications\CampaignStartReminderNotification;
use Illuminate\Support\Facades\Notification;

// ── CampaignStartReminderJob ──────────────────────────────────────────────────

test('start reminder job notifies all users for campaigns starting tomorrow', function () {
    Notification::fake();

    $user     = User::factory()->create();
    $tomorrow = now()->addDay()->toDateString();
    $campaign = Campaign::factory()->create(['start_date' => $tomorrow]);

    (new CampaignStartReminderJob())->handle();

    Notification::assertSentTo($user, CampaignStartReminderNotification::class,
        fn ($n) => $n->campaign->is($campaign),
    );
});

test('start reminder job does not notify when no campaigns start tomorrow', function () {
    Notification::fake();

    User::factory()->create();
    Campaign::factory()->create(['start_date' => now()->addDays(3)->toDateString()]);

    (new CampaignStartReminderJob())->handle();

    Notification::assertNothingSent();
});

test('start reminder job does not notify when no users exist', function () {
    Notification::fake();

    Campaign::factory()->create(['start_date' => now()->addDay()->toDateString()]);

    (new CampaignStartReminderJob())->handle();

    Notification::assertNothingSent();
});

test('start reminder job sends one notification per campaign', function () {
    Notification::fake();

    $user      = User::factory()->create();
    $tomorrow  = now()->addDay()->toDateString();
    Campaign::factory()->count(2)->create(['start_date' => $tomorrow]);

    (new CampaignStartReminderJob())->handle();

    Notification::assertSentToTimes($user, CampaignStartReminderNotification::class, 2);
});

// ── CampaignDeadlineReminderJob ───────────────────────────────────────────────

test('deadline reminder job notifies all users for campaigns ending tomorrow', function () {
    Notification::fake();

    $user     = User::factory()->create();
    $tomorrow = now()->addDay()->toDateString();
    $campaign = Campaign::factory()->create(['end_date' => $tomorrow]);

    (new CampaignDeadlineReminderJob())->handle();

    Notification::assertSentTo($user, CampaignDeadlineReminderNotification::class,
        fn ($n) => $n->campaign->is($campaign),
    );
});

test('deadline reminder job does not notify when no campaigns end tomorrow', function () {
    Notification::fake();

    User::factory()->create();
    Campaign::factory()->create(['end_date' => now()->addDays(5)->toDateString()]);

    (new CampaignDeadlineReminderJob())->handle();

    Notification::assertNothingSent();
});
