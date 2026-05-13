<?php

use App\Models\Campaign;
use App\Models\Invoice;
use App\Models\User;

test('authenticated user can download invoice pdf', function () {
    $user = User::factory()->create();
    $invoice = Invoice::factory()->create();
    $campaign = Campaign::find($invoice->campaign_id);

    $response = $this->actingAs($user)
        ->get(route('campaign.invoice.pdf', [$campaign, $invoice]));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
});

test('invoice pdf content disposition contains invoice id', function () {
    $user = User::factory()->create();
    $invoice = Invoice::factory()->create();
    $campaign = Campaign::find($invoice->campaign_id);

    $response = $this->actingAs($user)
        ->get(route('campaign.invoice.pdf', [$campaign, $invoice]));

    expect($response->headers->get('Content-Disposition'))
        ->toContain("invoice-{$invoice->id}.pdf");
});

test('guests cannot download invoice pdf', function () {
    $invoice = Invoice::factory()->create();
    $campaign = Campaign::find($invoice->campaign_id);

    $this->get(route('campaign.invoice.pdf', [$campaign, $invoice]))
        ->assertRedirect(route('login'));
});

test('pdf returns 404 when invoice does not belong to campaign', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $invoice = Invoice::factory()->create();

    $this->actingAs($user)
        ->get(route('campaign.invoice.pdf', [$campaign, $invoice]))
        ->assertNotFound();
});
