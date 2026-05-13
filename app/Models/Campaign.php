<?php

namespace App\Models;

use App\Traits\Models\Filterable;
use App\Traits\Models\HasPicture;
use App\Traits\Models\HasSlug;
use App\Traits\Models\Paginate;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;
    use Paginate;
    use Filterable;
    use HasPicture;
    use HasSlug;
    use HasUuids;

    protected $fillable = [
        'name', 'description', 'start_date', 'end_date',
        'status', 'banner_path',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    protected $appends = [
        'picture_url',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::deleting(function (Campaign $campaign) {
            $campaign->deletePicture();
        });
        static::creating(fn(self $model) => $model->slug = $model->generateSlug($model->name));
        static::updating(
            fn(self $model) => $model->slug = $model->isDirty('name') ?
                $model->generateSlug($model->name) : $model->slug,
        );
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->picturePathColumn = 'banner_path';
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function keyOpinionLeaders(): BelongsToMany
    {
        return $this->belongsToMany(KeyOpinionLeader::class)
            ->withPivot(['deliverable', 'posted_at', 'actual_views', 'actual_likes', 'actual_comments', 'actual_shares'])
            ->withTimestamps();
    }
}
