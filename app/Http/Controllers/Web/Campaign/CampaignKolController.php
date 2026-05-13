<?php

namespace App\Http\Controllers\Web\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\KeyOpinionLeader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CampaignKolController extends Controller
{
    public function store(Request $request, Campaign $campaign): RedirectResponse
    {
        $request->validate([
            'key_opinion_leader_id' => ['required', 'uuid', 'exists:key_opinion_leaders,id'],
            'deliverable' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $campaign->keyOpinionLeaders()->syncWithoutDetaching([
                $request->key_opinion_leader_id => [
                    'deliverable' => $request->deliverable,
                ],
            ]);

            return back()->with('success', 'KOL attached to campaign successfully.');
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return back()->with('error', 'Something went wrong.');
        }
    }

    public function destroy(Campaign $campaign, KeyOpinionLeader $keyOpinionLeader): RedirectResponse
    {
        try {
            $campaign->keyOpinionLeaders()->detach($keyOpinionLeader->id);

            return back()->with('success', 'KOL removed from campaign successfully.');
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return back()->with('error', 'Something went wrong.');
        }
    }
}
