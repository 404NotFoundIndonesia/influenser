<?php

namespace App\Jobs;

use App\Enum\Platform;
use App\Models\KeyOpinionLeader;
use App\Services\ThirdParty\CreatorDBServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncKolFromCreatorDB implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly KeyOpinionLeader $kol) {}

    public function handle(CreatorDBServiceInterface $service): void
    {
        $this->kol->update(['syncing_at' => now()]);

        try {
            $raw = match ($this->kol->platform) {
                Platform::TikTok->value    => $service->tiktokBasic($this->kol->username),
                Platform::Instagram->value => $service->instagramBasic($this->kol->username),
                Platform::Youtube->value   => $service->youtubeBasic($this->kol->username),
                Platform::Facebook->value  => $service->facebookBasic($this->kol->username),
                default                    => throw new \RuntimeException("Unsupported platform: {$this->kol->platform}"),
            };

            $metrics = $this->extractMetrics($raw);

            $this->kol->update(array_merge($metrics, [
                'synced_at'  => now(),
                'syncing_at' => null,
            ]));
        } catch (\Throwable $e) {
            $this->kol->update(['syncing_at' => null]);
            Log::error('SyncKolFromCreatorDB failed', [
                'kol_id' => $this->kol->id,
                'error'  => $e->getMessage(),
            ]);
        }
    }

    private function extractMetrics(array $data): array
    {
        return match ($this->kol->platform) {
            Platform::TikTok->value => [
                'followers'       => $data['followers']   ?? $this->kol->followers,
                'following'       => $data['following']   ?? $this->kol->following,
                'total_content'   => $data['videos']      ?? $this->kol->total_content,
                'likes'           => $data['hearts']      ?? $this->kol->likes,
                'avg_views'       => $data['avgPlays']    ?? $this->kol->avg_views,
                'avg_likes'       => $data['avgHearts']   ?? $this->kol->avg_likes,
                'avg_shares'      => $data['avgShares']   ?? $this->kol->avg_shares,
                'avg_comments'    => $data['avgComments'] ?? $this->kol->avg_comments,
                'engagement_rate' => $data['engageRate']  ?? $this->kol->engagement_rate,
            ],
            Platform::Instagram->value => [
                'followers'       => $data['followers']   ?? $this->kol->followers,
                'following'       => $data['following']   ?? $this->kol->following,
                'total_content'   => $data['posts']       ?? $this->kol->total_content,
                'avg_likes'       => $data['avgLikes']    ?? $this->kol->avg_likes,
                'avg_comments'    => $data['avgComments'] ?? $this->kol->avg_comments,
                'engagement_rate' => $data['engageRate']  ?? $this->kol->engagement_rate,
            ],
            Platform::Youtube->value => [
                'followers'       => $data['subscribers']   ?? $this->kol->followers,
                'total_content'   => $data['videos']        ?? $this->kol->total_content,
                'views'           => $data['totalViews']    ?? $this->kol->views,
                'avg_views'       => $data['avgViews1Y']    ?? $this->kol->avg_views,
                'avg_likes'       => $data['avgLikes1Y']    ?? $this->kol->avg_likes,
                'avg_comments'    => $data['avgComments1Y'] ?? $this->kol->avg_comments,
                'engagement_rate' => $data['engageRate1Y']  ?? $this->kol->engagement_rate,
            ],
            Platform::Facebook->value => [
                'followers'       => $data['followers']    ?? $this->kol->followers,
                'following'       => $data['following']    ?? $this->kol->following,
                'total_content'   => $data['posts']        ?? $this->kol->total_content,
                'likes'           => $data['likes']        ?? $this->kol->likes,
                'avg_views'       => $data['avgPlays']     ?? $this->kol->avg_views,
                'avg_likes'       => $data['avgReactions'] ?? $this->kol->avg_likes,
                'avg_shares'      => $data['avgShares']    ?? $this->kol->avg_shares,
                'avg_comments'    => $data['avgComments']  ?? $this->kol->avg_comments,
            ],
            default => [],
        };
    }
}
