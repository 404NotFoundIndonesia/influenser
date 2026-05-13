<?php

namespace App\Http\Controllers\Web;

use App\Enum\CampaignStatus;
use App\Enum\InvoiceStatus;
use App\Models\Campaign;
use App\Models\Influencer;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController
{
    public function __invoke(Request $request): Response
    {
        $totalInfluencers = Influencer::count();
        $totalCampaigns = Campaign::count();
        $activeCampaigns = Campaign::where('status', CampaignStatus::Ongoing)->count();
        $totalInvoiced = Invoice::sum('amount');
        $totalPaid = Invoice::where('status', InvoiceStatus::Paid)->sum('amount');

        $topInfluencers = Influencer::with('key_opinion_leaders')
            ->get()
            ->map(fn (Influencer $influencer) => [
                'id' => $influencer->id,
                'name' => $influencer->name,
                'picture_url' => $influencer->picture_url,
                'avg_engagement' => $influencer->key_opinion_leaders->avg('engagement_rate') ?? 0,
                'kol_count' => $influencer->key_opinion_leaders->count(),
                'platforms' => $influencer->key_opinion_leaders->pluck('platform')->unique()->values(),
            ])
            ->sortByDesc('avg_engagement')
            ->take(5)
            ->values();

        $campaignStatusBreakdown = collect(CampaignStatus::cases())
            ->mapWithKeys(fn (CampaignStatus $status) => [
                $status->value => Campaign::where('status', $status)->count(),
            ]);

        return Inertia::render('Dashboard', [
            'totalInfluencers' => $totalInfluencers,
            'totalCampaigns' => $totalCampaigns,
            'activeCampaigns' => $activeCampaigns,
            'totalInvoiced' => (float) $totalInvoiced,
            'totalPaid' => (float) $totalPaid,
            'topInfluencers' => $topInfluencers,
            'campaignStatusBreakdown' => $campaignStatusBreakdown,
        ]);
    }
}
