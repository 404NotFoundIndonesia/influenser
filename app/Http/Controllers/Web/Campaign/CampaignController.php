<?php

namespace App\Http\Controllers\Web\Campaign;

use App\Http\Controllers\Controller;
use App\Http\Requests\Campaign\StoreCampaignRequest;
use App\Http\Requests\Campaign\UpdateCampaignRequest;
use App\Models\Campaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class CampaignController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('campaign/Index', [
            'items' => Campaign::query()
                ->filter($request->query('filter'))
                ->render($request->query('size')),
        ]);
    }

    public function show(Campaign $campaign): Response
    {
        return Inertia::render('campaign/Show', [
            'item' => $campaign,
        ]);
    }

    public function store(StoreCampaignRequest $request): RedirectResponse
    {
        try {
            $input = $request->validated();
            Campaign::query()->create($input);

            return back()->with('success', 'Campaign created successfully');
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', 'Something went wrong');
        }
    }

    public function update(UpdateCampaignRequest $request, Campaign $campaign): RedirectResponse
    {
        try {
            $input = $request->validated();
            $campaign->update($input);

            return back()->with('success', 'Campaign updated successfully');
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', 'Something went wrong');
        }
    }

    public function destroy(Campaign $campaign): RedirectResponse
    {
        try {
            $campaign->delete();

            return back()->with('success', 'Campaign deleted successfully');
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', 'Something went wrong');
        }
    }

    public function massDestroy(Request $request): RedirectResponse
    {
        try {
            Campaign::query()
                ->whereIn('id', $request->input('ids', []))
                ->delete();

            return back()->with('success', 'Campaign deleted successfully.');
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return back()->with('error', 'Something went wrong.');
        }
    }
}
