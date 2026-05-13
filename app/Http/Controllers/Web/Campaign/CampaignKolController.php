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

    public function update(Request $request, Campaign $campaign, KeyOpinionLeader $keyOpinionLeader): RedirectResponse
    {
        if (!$campaign->keyOpinionLeaders()->where('key_opinion_leaders.id', $keyOpinionLeader->id)->exists()) {
            abort(404);
        }

        $request->validate([
            'actual_views'    => ['nullable', 'integer', 'min:0'],
            'actual_likes'    => ['nullable', 'integer', 'min:0'],
            'actual_comments' => ['nullable', 'integer', 'min:0'],
            'actual_shares'   => ['nullable', 'integer', 'min:0'],
            'posted_at'       => ['nullable', 'date'],
        ]);

        try {
            $campaign->keyOpinionLeaders()->updateExistingPivot($keyOpinionLeader->id, [
                'actual_views'    => $request->actual_views,
                'actual_likes'    => $request->actual_likes,
                'actual_comments' => $request->actual_comments,
                'actual_shares'   => $request->actual_shares,
                'posted_at'       => $request->posted_at,
            ]);

            return back()->with('success', 'Engagement metrics updated.');
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
