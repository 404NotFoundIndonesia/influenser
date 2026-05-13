<?php

namespace App\Models;

use App\Traits\Models\Filterable;
use App\Traits\Models\HasPicture;
use App\Traits\Models\Paginate;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Influencer extends Model
{
    use Filterable;
    use HasFactory;
    use HasPicture;
    use HasUuids;
    use Notifiable;
    use Paginate;

    protected static function boot(): void
    {
        parent::boot();
        static::deleting(function (Influencer $influencer) {
            $influencer->deletePicture();
        });
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->picturePathColumn = 'profile_picture_path';
    }

    protected $fillable = [
        'name', 'bio', 'location', 'phone', 'whatsapp',
        'email', 'status', 'profile_picture_path',
    ];

    protected $appends = [
        'picture_url',
    ];

    public function key_opinion_leaders(): HasMany
    {
        return $this->hasMany(KeyOpinionLeader::class);
    }

    public function niches(): BelongsToMany
    {
        return $this
            ->belongsToMany(Niche::class, 'influencer_niche')
            ->withTimestamps();
    }
}
