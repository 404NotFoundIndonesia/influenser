<?php

namespace App\Models;

use App\Traits\Models\HasPicture;
use App\Traits\Models\Paginate;
use App\Traits\Models\Filterable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Influencer extends Model
{
    use HasFactory;
    use HasUuids;
    use HasPicture;
    use Paginate;
    use Filterable;

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
}
