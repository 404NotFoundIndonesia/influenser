<?php

namespace App\Services\ThirdParty;

use App\Enum\Platform;
use App\Models\KeyOpinionLeader;

class ApifyKolSyncService
{
    public function __construct(private readonly ApifyServiceInterface $apify) {}

    public function sync(KeyOpinionLeader $kol): array
    {
        $platform = $kol->platform;
        $actorId = config("influenser.apify.actors.{$platform}");

        if (! $actorId || ! $this->isSupportedPlatform($platform)) {
            throw new \RuntimeException("Unsupported Apify platform: {$platform}");
        }

        $input = $this->buildInput($platform, $kol->username);
        $items = $this->apify->runActor($actorId, $input);
        $raw = $items[0] ?? [];

        return $this->normalize($platform, $raw);
    }

    private function isSupportedPlatform(string $platform): bool
    {
        return in_array($platform, [
            Platform::TikTok->value,
            Platform::Instagram->value,
            Platform::Youtube->value,
            Platform::Facebook->value,
        ], true);
    }

    private function buildInput(string $platform, string $username): array
    {
        return match ($platform) {
            Platform::TikTok->value => ['profiles' => [$username]],
            Platform::Instagram->value => ['usernames' => [$username]],
            Platform::Youtube->value => ['channelUrls' => ["https://www.youtube.com/{$username}"]],
            Platform::Facebook->value => ['startUrls' => [['url' => "https://www.facebook.com/{$username}"]]],
            default => ['username' => $username],
        };
    }

    private function normalize(string $platform, array $raw): array
    {
        return match ($platform) {
            Platform::TikTok->value => [
                'followers' => $raw['fans'] ?? $raw['followers'] ?? 0,
                'following' => $raw['following'] ?? 0,
                'total_content' => $raw['video'] ?? $raw['videoCount'] ?? 0,
                'likes' => $raw['heart'] ?? $raw['likes'] ?? 0,
                'views' => $raw['playCount'] ?? 0,
                'avg_views' => $raw['avgViews'] ?? 0,
                'avg_likes' => $raw['avgLikes'] ?? 0,
                'avg_shares' => $raw['avgShares'] ?? 0,
                'avg_comments' => $raw['avgComments'] ?? 0,
                'engagement_rate' => $raw['engagementRate'] ?? 0,
            ],
            Platform::Instagram->value => [
                'followers' => $raw['followersCount'] ?? $raw['followers'] ?? 0,
                'following' => $raw['followsCount'] ?? $raw['following'] ?? 0,
                'total_content' => $raw['postsCount'] ?? $raw['posts'] ?? 0,
                'likes' => 0,
                'views' => 0,
                'avg_views' => 0,
                'avg_likes' => $raw['avgLikes'] ?? 0,
                'avg_shares' => 0,
                'avg_comments' => $raw['avgComments'] ?? $raw['avgReels'] ?? 0,
                'engagement_rate' => $raw['engagementRate'] ?? $raw['engagement'] ?? 0,
            ],
            Platform::Youtube->value => [
                'followers' => $raw['numberOfSubscribers'] ?? $raw['subscribers'] ?? 0,
                'following' => 0,
                'total_content' => $raw['numberOfVideos'] ?? $raw['videoCount'] ?? 0,
                'likes' => 0,
                'views' => $raw['numberOfViews'] ?? $raw['views'] ?? 0,
                'avg_views' => $raw['avgViews'] ?? 0,
                'avg_likes' => $raw['avgLikes'] ?? 0,
                'avg_shares' => 0,
                'avg_comments' => $raw['avgComments'] ?? 0,
                'engagement_rate' => $raw['engagementRate'] ?? 0,
            ],
            Platform::Facebook->value => [
                'followers' => $raw['followers'] ?? $raw['likes'] ?? 0,
                'following' => $raw['following'] ?? 0,
                'total_content' => $raw['posts'] ?? 0,
                'likes' => $raw['likes'] ?? 0,
                'views' => 0,
                'avg_views' => $raw['avgViews'] ?? 0,
                'avg_likes' => $raw['avgReactions'] ?? $raw['avgLikes'] ?? 0,
                'avg_shares' => $raw['avgShares'] ?? 0,
                'avg_comments' => $raw['avgComments'] ?? 0,
                'engagement_rate' => $raw['engagementRate'] ?? 0,
            ],
            default => [],
        };
    }
}
