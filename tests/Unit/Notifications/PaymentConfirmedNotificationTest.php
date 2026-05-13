<?php

use App\Models\Campaign;
use App\Models\Influencer;
use App\Models\Invoice;
use App\Notifications\PaymentConfirmedNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification;

// ── via() ─────────────────────────────────────────────────────────────────────

test('payment confirmed notification is sent via mail', function () {
    $campaign = new Campaign(['name' => 'Test Campaign']);
    $invoice = new Invoice(['amount' => 500_000]);
    $invoice->setRelation('campaign', $campaign);

    $notification = new PaymentConfirmedNotification($invoice);

    expect($notification->via(new Influencer))->toBe(['mail']);
});

// ── toMail() ──────────────────────────────────────────────────────────────────

test('payment confirmed notification has correct subject', function () {
    $campaign = new Campaign(['name' => 'Holiday Push']);
    $invoice = new Invoice(['amount' => 1_500_000]);
    $invoice->setRelation('campaign', $campaign);

    $mail = (new PaymentConfirmedNotification($invoice))
        ->toMail(new Influencer(['name' => 'Jane Doe', 'email' => 'jane@example.com']));

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe('Payment Confirmed — Holiday Push');
});

test('payment confirmed notification mail contains invoice amount in view data', function () {
    $campaign = new Campaign(['name' => 'Year End']);
    $invoice = new Invoice(['amount' => 2_000_000]);
    $invoice->setRelation('campaign', $campaign);

    $mail = (new PaymentConfirmedNotification($invoice))
        ->toMail(new Influencer(['name' => 'Bob', 'email' => 'bob@example.com']));

    expect((float) $mail->viewData['invoice']->amount)->toBe(2_000_000.0);
});

// ── Notification::assertSentTo ────────────────────────────────────────────────

test('payment confirmed notification targets the influencer email', function () {
    Notification::fake();

    $influencer = new Influencer(['name' => 'Creator', 'email' => 'creator@test.com']);
    $campaign = new Campaign(['name' => 'Brand Deal']);
    $invoice = new Invoice(['amount' => 750_000]);
    $invoice->setRelation('campaign', $campaign);

    $influencer->notify(new PaymentConfirmedNotification($invoice));

    Notification::assertSentTo(
        $influencer,
        PaymentConfirmedNotification::class,
        fn ($notification) => (float) $notification->invoice->amount === 750_000.0,
    );
});
