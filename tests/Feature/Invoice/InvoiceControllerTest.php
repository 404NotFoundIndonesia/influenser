<?php

use App\Enum\InvoiceStatus;
use App\Models\Campaign;
use App\Models\Influencer;
use App\Models\Invoice;
use App\Models\KeyOpinionLeader;
use App\Models\User;
use App\Notifications\PaymentConfirmedNotification;
use Illuminate\Support\Facades\Notification;

// ── Auth guard ────────────────────────────────────────────────────────────────

test('guests cannot create an invoice', function () {
    $campaign = Campaign::factory()->create();

    $this->post(route('campaign.invoice.store', $campaign), [])
        ->assertRedirect(route('login'));
});

test('guests cannot update an invoice', function () {
    $invoice  = Invoice::factory()->create();
    $campaign = Campaign::find($invoice->campaign_id);

    $this->put(route('campaign.invoice.update', [$campaign, $invoice]), [])
        ->assertRedirect(route('login'));
});

test('guests cannot delete an invoice', function () {
    $invoice  = Invoice::factory()->create();
    $campaign = Campaign::find($invoice->campaign_id);

    $this->delete(route('campaign.invoice.destroy', [$campaign, $invoice]))
        ->assertRedirect(route('login'));
});

// ── Store ─────────────────────────────────────────────────────────────────────

test('store creates invoice with kol endorsement rate as default amount', function () {
    $user       = User::factory()->create();
    $campaign   = Campaign::factory()->create();
    $influencer = Influencer::factory()->create();
    $kol        = KeyOpinionLeader::factory()->create([
        'influencer_id'   => $influencer->id,
        'endorsement_rate' => 2_500_000,
    ]);

    $this->actingAs($user)
        ->post(route('campaign.invoice.store', $campaign), [
            'influencer_id'         => $influencer->id,
            'key_opinion_leader_id' => $kol->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('invoices', [
        'campaign_id'           => $campaign->id,
        'influencer_id'         => $influencer->id,
        'key_opinion_leader_id' => $kol->id,
        'amount'                => '2500000.00',
        'status'                => InvoiceStatus::Unpaid->value,
    ]);
});

test('store uses explicit amount when provided', function () {
    $user       = User::factory()->create();
    $campaign   = Campaign::factory()->create();
    $influencer = Influencer::factory()->create();
    $kol        = KeyOpinionLeader::factory()->create([
        'influencer_id'   => $influencer->id,
        'endorsement_rate' => 2_500_000,
    ]);

    $this->actingAs($user)
        ->post(route('campaign.invoice.store', $campaign), [
            'influencer_id'         => $influencer->id,
            'key_opinion_leader_id' => $kol->id,
            'amount'                => 1_000_000,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('invoices', [
        'campaign_id' => $campaign->id,
        'amount'      => '1000000.00',
    ]);
});

test('store returns 422 when influencer_id is missing', function () {
    $user     = User::factory()->create();
    $campaign = Campaign::factory()->create();

    $this->actingAs($user)
        ->post(route('campaign.invoice.store', $campaign), [])
        ->assertSessionHasErrors('influencer_id');
});

// ── Update ────────────────────────────────────────────────────────────────────

test('update sets paid_at when status changes to paid', function () {
    $user    = User::factory()->create();
    $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Unpaid, 'paid_at' => null]);
    $campaign = Campaign::find($invoice->campaign_id);

    $this->actingAs($user)
        ->put(route('campaign.invoice.update', [$campaign, $invoice]), [
            'status' => InvoiceStatus::Paid->value,
        ])
        ->assertRedirect();

    $invoice->refresh();
    expect($invoice->status)->toBe(InvoiceStatus::Paid)
        ->and($invoice->paid_at)->not->toBeNull();
});

test('update does not set paid_at when status is pending', function () {
    $user    = User::factory()->create();
    $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Unpaid, 'paid_at' => null]);
    $campaign = Campaign::find($invoice->campaign_id);

    $this->actingAs($user)
        ->put(route('campaign.invoice.update', [$campaign, $invoice]), [
            'status' => InvoiceStatus::Pending->value,
        ])
        ->assertRedirect();

    $invoice->refresh();
    expect($invoice->status)->toBe(InvoiceStatus::Pending)
        ->and($invoice->paid_at)->toBeNull();
});

test('update returns 404 when invoice does not belong to campaign', function () {
    $user     = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $invoice  = Invoice::factory()->create();

    $this->actingAs($user)
        ->put(route('campaign.invoice.update', [$campaign, $invoice]), [
            'status' => InvoiceStatus::Paid->value,
        ])
        ->assertNotFound();
});

test('update returns 422 when status value is invalid', function () {
    $user    = User::factory()->create();
    $invoice = Invoice::factory()->create();
    $campaign = Campaign::find($invoice->campaign_id);

    $this->actingAs($user)
        ->put(route('campaign.invoice.update', [$campaign, $invoice]), [
            'status' => 'invalid-status',
        ])
        ->assertSessionHasErrors('status');
});

// ── Destroy ───────────────────────────────────────────────────────────────────

test('destroy removes invoice from database', function () {
    $user    = User::factory()->create();
    $invoice = Invoice::factory()->create();
    $campaign = Campaign::find($invoice->campaign_id);

    $this->actingAs($user)
        ->delete(route('campaign.invoice.destroy', [$campaign, $invoice]))
        ->assertRedirect();

    $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
});

test('destroy returns 404 when invoice does not belong to campaign', function () {
    $user     = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $invoice  = Invoice::factory()->create();

    $this->actingAs($user)
        ->delete(route('campaign.invoice.destroy', [$campaign, $invoice]))
        ->assertNotFound();
});

// ── T3.4 — PaymentConfirmedNotification ──────────────────────────────────────

test('updating invoice to paid dispatches payment confirmed notification to influencer', function () {
    Notification::fake();

    $user       = User::factory()->create();
    $influencer = Influencer::factory()->create(['email' => 'creator@example.com']);
    $invoice    = Invoice::factory()->create([
        'influencer_id' => $influencer->id,
        'status'        => InvoiceStatus::Unpaid,
    ]);
    $campaign = Campaign::find($invoice->campaign_id);

    $this->actingAs($user)
        ->put(route('campaign.invoice.update', [$campaign, $invoice]), [
            'status' => InvoiceStatus::Paid->value,
        ]);

    Notification::assertSentTo($influencer, PaymentConfirmedNotification::class);
});

test('updating invoice to paid does not notify when influencer has no email', function () {
    Notification::fake();

    $user       = User::factory()->create();
    $influencer = Influencer::factory()->create(['email' => null]);
    $invoice    = Invoice::factory()->create([
        'influencer_id' => $influencer->id,
        'status'        => InvoiceStatus::Unpaid,
    ]);
    $campaign = Campaign::find($invoice->campaign_id);

    $this->actingAs($user)
        ->put(route('campaign.invoice.update', [$campaign, $invoice]), [
            'status' => InvoiceStatus::Paid->value,
        ]);

    Notification::assertNothingSent();
});

// ── Campaign Show props ───────────────────────────────────────────────────────

test('campaign show includes invoices prop', function () {
    $user     = User::factory()->create();
    $campaign = Campaign::factory()->create();
    Invoice::factory()->count(2)->create(['campaign_id' => $campaign->id]);

    $this->actingAs($user)
        ->withoutVite()
        ->get(route('campaign.show', $campaign))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('campaign/Show')
            ->has('invoices', 2)
        );
});
