<?php

namespace App\Http\Controllers\Web\CreatorDB;

use App\Enum\Platform;
use App\Jobs\SyncKolFromApify;
use App\Jobs\SyncKolFromCreatorDB;
use App\Models\Influencer;
use App\Models\KeyOpinionLeader;
use App\Services\ThirdParty\CreatorDBServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CreatorDBController
{
    public function __construct(private readonly CreatorDBServiceInterface $service) {}

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'platform' => ['required', Rule::in(Platform::values())],
            'username' => ['required', 'string'],
        ]);

        try {
            $raw = $this->fetchFromCreatorDB($validated['platform'], $validated['username']);

            return response()->json($this->normalize($validated['platform'], $validated['username'], $raw));
        } catch (\Throwable $e) {
            Log::error('CreatorDB search error', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'CreatorDB API error'], 502);
        }
    }

    public function import(Request $request, Influencer $influencer): RedirectResponse
    {
        $validated = $request->validate([
            'platform' => ['required', Rule::in(Platform::values())],
            'username' => ['required', 'string'],
        ]);

        try {
            $raw = $this->fetchFromCreatorDB($validated['platform'], $validated['username']);
            $normalized = $this->normalize($validated['platform'], $validated['username'], $raw);

            $influencer->key_opinion_leaders()->create(array_merge($normalized, [
                'synced_at' => now(),
            ]));
        } catch (\Throwable $e) {
            Log::error('CreatorDB import error', ['error' => $e->getMessage()]);

            return back()->withErrors(['message' => 'Failed to import KOL from CreatorDB.']);
        }

        return redirect()->route('influencer.show', $influencer)->with('success', 'KOL imported successfully.');
    }

    public function sync(Influencer $influencer, KeyOpinionLeader $keyOpinionLeader): RedirectResponse
    {
        SyncKolFromCreatorDB::dispatch($keyOpinionLeader);

        return back();
    }

    public function syncApify(Influencer $influencer, KeyOpinionLeader $keyOpinionLeader): RedirectResponse
    {
        SyncKolFromApify::dispatch($keyOpinionLeader);

        return back();
    }

    private function fetchFromCreatorDB(string $platform, string $username): array
    {
        return match ($platform) {
            Platform::TikTok->value => $this->service->tiktokBasic($username),
            Platform::Instagram->value => $this->service->instagramBasic($username),
            Platform::Youtube->value => $this->service->youtubeBasic($username),
            Platform::Facebook->value => $this->service->facebookBasic($username),
            default => throw new \RuntimeException("Unsupported platform: {$platform}"),
        };
    }

    private function normalize(string $platform, string $username, array $data): array
    {
        $base = [
            'platform' => $platform,
            'username' => $data[$this->usernameKey($platform)] ?? $username,
            'link' => Platform::from($platform)->profileUrl($username),
        ];

        $metrics = match ($platform) {
            Platform::TikTok->value => [
                'followers' => $data['followers'] ?? 0,
                'following' => $data['following'] ?? 0,
                'total_content' => $data['videos'] ?? 0,
                'views' => $data['hearts'] ?? 0,
                'likes' => $data['hearts'] ?? 0,
                'shares' => 0,
                'comments' => 0,
                'avg_views' => $data['avgPlays'] ?? 0,
                'avg_likes' => $data['avgHearts'] ?? 0,
                'avg_shares' => $data['avgShares'] ?? 0,
                'avg_comments' => $data['avgComments'] ?? 0,
                'engagement_rate' => $data['engageRate'] ?? 0,
            ],
            Platform::Instagram->value => [
                'followers' => $data['followers'] ?? 0,
                'following' => $data['following'] ?? 0,
                'total_content' => $data['posts'] ?? 0,
                'views' => 0,
                'likes' => 0,
                'shares' => 0,
                'comments' => 0,
                'avg_views' => 0,
                'avg_likes' => $data['avgLikes'] ?? 0,
                'avg_shares' => 0,
                'avg_comments' => $data['avgComments'] ?? 0,
                'engagement_rate' => $data['engageRate'] ?? 0,
            ],
            Platform::Youtube->value => [
                'followers' => $data['subscribers'] ?? 0,
                'following' => 0,
                'total_content' => $data['videos'] ?? 0,
                'views' => $data['totalViews'] ?? 0,
                'likes' => 0,
                'shares' => 0,
                'comments' => 0,
                'avg_views' => $data['avgViews1Y'] ?? 0,
                'avg_likes' => $data['avgLikes1Y'] ?? 0,
                'avg_shares' => 0,
                'avg_comments' => $data['avgComments1Y'] ?? 0,
                'engagement_rate' => $data['engageRate1Y'] ?? 0,
            ],
            Platform::Facebook->value => [
                'followers' => $data['followers'] ?? 0,
                'following' => $data['following'] ?? 0,
                'total_content' => $data['posts'] ?? 0,
                'views' => 0,
                'likes' => $data['likes'] ?? 0,
                'shares' => 0,
                'comments' => 0,
                'avg_views' => $data['avgPlays'] ?? 0,
                'avg_likes' => $data['avgReactions'] ?? 0,
                'avg_shares' => $data['avgShares'] ?? 0,
                'avg_comments' => $data['avgComments'] ?? 0,
                'engagement_rate' => 0,
            ],
            default => [],
        };

        return array_merge($base, $metrics);
    }

    private function usernameKey(string $platform): string
    {
        return match ($platform) {
            Platform::TikTok->value => 'tiktokId',
            Platform::Instagram->value => 'instagramId',
            Platform::Youtube->value => 'youtubeId',
            Platform::Facebook->value => 'facebookId',
            default => 'username',
        };
    }
}
