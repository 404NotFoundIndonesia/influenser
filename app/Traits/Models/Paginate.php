<?php

namespace App\Traits\Models;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

trait Paginate
{
    protected int $sizePerPage = 10;

    public function scopeRender(Builder $query, ?int $page = null): LengthAwarePaginator
    {
        return $query
//            ->whereHas('key_opinion_leaders', function (Builder $query) {
//                $query->whereIn('platform', ['Telegram',]);
//            })
            ->paginate($page ?? $this->sizePerPage)
            ->withQueryString();
    }
}
