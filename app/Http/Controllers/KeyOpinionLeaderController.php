<?php

namespace App\Http\Controllers;

use App\Models\Influencer;
use App\Models\KeyOpinionLeader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class KeyOpinionLeaderController extends Controller
{
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Influencer $influencer, KeyOpinionLeader $keyOpinionLeader): RedirectResponse
    {
        try {
            $keyOpinionLeader->delete();

            return back()->with('success', 'Key opinion leader has been deleted.');
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return back()->with('error', 'Something went wrong.');
        }
    }
}
