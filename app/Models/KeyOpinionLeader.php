<?php

namespace App\Models;

use App\Enum\Platform;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class KeyOpinionLeader extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'username', 'platform', 'link', 'bio',
        'engagement_rate', 'followers', 'following', 'total_content',
        'views', 'likes', 'shares', 'comments',
        'avg_views', 'avg_likes', 'avg_shares', 'avg_comments',
        'endorsement_rate', 'custom', 'synced_at', 'syncing_at',
    ];

    protected $casts = [
        'custom' => 'array',
    ];

    protected $appends = [
        'is_syncing', 'platform_name',
    ];

    public function isSyncing(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->syncing_at)
                return false;

            return now() < Carbon::parse($this->syncing_at)->addDay();
        });
    }

    public function platformName(): Attribute
    {
        return Attribute::get(fn () => Platform::tryFrom($this->platform)?->name);
    }

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(Influencer::class);
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class)
            ->withPivot(['deliverable', 'posted_at', 'actual_views', 'actual_likes', 'actual_comments', 'actual_shares'])
            ->withTimestamps();
    }
}
