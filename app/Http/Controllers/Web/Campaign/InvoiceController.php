<?php

namespace App\Http\Controllers\Web\Campaign;

use App\Enum\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Invoice;
use App\Models\KeyOpinionLeader;
use App\Notifications\PaymentConfirmedNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function index(Campaign $campaign): RedirectResponse
    {
        return redirect()->route('campaign.show', $campaign);
    }

    public function store(Request $request, Campaign $campaign): RedirectResponse
    {
        $request->validate([
            'key_opinion_leader_id' => ['nullable', 'uuid', 'exists:key_opinion_leaders,id'],
            'influencer_id' => ['required', 'uuid', 'exists:influencers,id'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $amount = $request->amount;

            if ($amount === null && $request->key_opinion_leader_id) {
                $kol = KeyOpinionLeader::find($request->key_opinion_leader_id);
                $amount = $kol?->endorsement_rate ?? 0;
            }

            $campaign->invoices()->create([
                'influencer_id' => $request->influencer_id,
                'key_opinion_leader_id' => $request->key_opinion_leader_id,
                'amount' => $amount ?? 0,
                'notes' => $request->notes,
                'status' => InvoiceStatus::Unpaid,
            ]);

            return back()->with('success', 'Invoice created successfully.');
        } catch (\Throwable $e) {
            Log::error($e->getMessage(), $e->getTrace());

            return back()->with('error', 'Something went wrong.');
        }
    }

    public function update(Request $request, Campaign $campaign, Invoice $invoice): RedirectResponse
    {
        if ($invoice->campaign_id !== $campaign->id) {
            abort(404);
        }

        $request->validate([
            'status' => ['nullable', Rule::in(InvoiceStatus::values())],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'proof' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'],
        ]);

        try {
            $data = array_filter([
                'amount' => $request->amount,
                'notes' => $request->notes,
            ], fn ($v) => $v !== null);

            if ($request->filled('status')) {
                $newStatus = InvoiceStatus::from($request->status);
                $data['status'] = $newStatus;

                if ($newStatus === InvoiceStatus::Paid && $invoice->status !== InvoiceStatus::Paid) {
                    $data['paid_at'] = now();

                    $invoice->load(['influencer', 'campaign']);
                    if ($invoice->influencer?->email) {
                        $invoice->influencer->notify(
                            new PaymentConfirmedNotification($invoice)
                        );
                    }
                }
            }

            if ($request->hasFile('proof')) {
                if ($invoice->proof_path) {
                    Storage::delete($invoice->proof_path);
                }
                $data['proof_path'] = $request->file('proof')->store('invoices/proofs', 'public');
            }

            $invoice->update($data);

            return back()->with('success', 'Invoice updated successfully.');
        } catch (\Throwable $e) {
            Log::error($e->getMessage(), $e->getTrace());

            return back()->with('error', 'Something went wrong.');
        }
    }

    public function destroy(Campaign $campaign, Invoice $invoice): RedirectResponse
    {
        if ($invoice->campaign_id !== $campaign->id) {
            abort(404);
        }

        try {
            if ($invoice->proof_path) {
                Storage::delete($invoice->proof_path);
            }
            $invoice->delete();

            return back()->with('success', 'Invoice deleted successfully.');
        } catch (\Throwable $e) {
            Log::error($e->getMessage(), $e->getTrace());

            return back()->with('error', 'Something went wrong.');
        }
    }

    public function pdf(Campaign $campaign, Invoice $invoice): HttpResponse
    {
        if ($invoice->campaign_id !== $campaign->id) {
            abort(404);
        }

        $invoice->load(['campaign', 'influencer', 'keyOpinionLeader']);

        $pivot = $invoice->keyOpinionLeader
            ? $campaign->keyOpinionLeaders()
                ->where('key_opinion_leaders.id', $invoice->key_opinion_leader_id)
                ->first()?->pivot
            : null;

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'campaign' => $invoice->campaign,
            'pivot' => $pivot,
        ]);

        return $pdf->download("invoice-{$invoice->id}.pdf");
    }
}
