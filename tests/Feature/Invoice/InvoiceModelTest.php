<?php

use App\Enum\InvoiceStatus;
use App\Models\Campaign;
use App\Models\Influencer;
use App\Models\Invoice;
use App\Models\KeyOpinionLeader;

// ── Relationships ─────────────────────────────────────────────────────────────

test('invoice belongs to campaign', function () {
    $campaign = Campaign::factory()->create();
    $invoice  = Invoice::factory()->create(['campaign_id' => $campaign->id]);

    expect($invoice->campaign)->toBeInstanceOf(Campaign::class)
        ->and($invoice->campaign->id)->toBe($campaign->id);
});

test('invoice belongs to influencer', function () {
    $influencer = Influencer::factory()->create();
    $invoice    = Invoice::factory()->create(['influencer_id' => $influencer->id]);

    expect($invoice->influencer)->toBeInstanceOf(Influencer::class)
        ->and($invoice->influencer->id)->toBe($influencer->id);
});

test('invoice belongs to key opinion leader', function () {
    $influencer = Influencer::factory()->create();
    $kol        = KeyOpinionLeader::factory()->create(['influencer_id' => $influencer->id]);
    $invoice    = Invoice::factory()->create([
        'influencer_id'         => $influencer->id,
        'key_opinion_leader_id' => $kol->id,
    ]);

    expect($invoice->keyOpinionLeader)->toBeInstanceOf(KeyOpinionLeader::class)
        ->and($invoice->keyOpinionLeader->id)->toBe($kol->id);
});

test('invoice key opinion leader is nullable', function () {
    $invoice = Invoice::factory()->create(['key_opinion_leader_id' => null]);

    expect($invoice->keyOpinionLeader)->toBeNull();
});

// ── InvoiceStatus enum ────────────────────────────────────────────────────────

test('invoice status enum has exactly three cases', function () {
    expect(InvoiceStatus::cases())->toHaveCount(3);
});

test('invoice status enum has correct values', function () {
    expect(InvoiceStatus::Unpaid->value)->toBe('unpaid')
        ->and(InvoiceStatus::Pending->value)->toBe('pending')
        ->and(InvoiceStatus::Paid->value)->toBe('paid');
});

test('invoice status is cast to enum', function () {
    $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Paid]);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Paid);
});
