<?php

namespace App\Models;

use App\Traits\Models\Filterable;
use App\Traits\Models\HasSlug;
use App\Traits\Models\Paginate;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Niche extends Model
{
    use HasFactory;
    use HasUuids;
    use Paginate;
    use Filterable;
    use HasSlug;

    protected $fillable = [
        'name', 'description', 'icon', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn(self $model) => $model->slug = $model->generateSlug($model->name));
        static::updating(
            fn(self $model) => $model->slug = $model->isDirty('name') ?
                $model->generateSlug($model->name) : $model->slug,
        );
    }

    public function influencers(): BelongsToMany
    {
        return $this->belongsToMany(Influencer::class, 'influencer_niche')
            ->withTimestamps();
    }
}
