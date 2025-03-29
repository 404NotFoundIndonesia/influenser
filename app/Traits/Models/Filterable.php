<?php

namespace App\Traits\Models;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    public function scopeFilter(Builder $query, ?array $filters): void
    {
        $query->when($filters, function (Builder $query) use ($filters) {
            foreach ($filters as $column => $filter) {
                $value = $filter['value'];
                if (empty($value)) {
                    continue;
                }

                $matchMode = $filter['matchMode'];
                if (str_contains($column, ".")) {
                    [$relation, $column] = explode('.', $column);
                    $query->whereHas($relation, function (Builder $query) use ($column, $matchMode, $value) {
                        $this->applyFilter($matchMode, $query, $column, $value);
                    });
                } else {
                    $this->applyFilter($matchMode, $query, $column, $value);
                }
            }
        });
    }

    /**
     * @param mixed $matchMode
     * @param Builder $query
     * @param int|string $column
     * @param mixed $value
     * @return void
     */
    private function applyFilter(mixed $matchMode, Builder $query, int|string $column, mixed $value): void
    {
        switch ($matchMode) {
            case 'equals':
                is_array($value) ? $query->whereIn($column, array_values(array_unique($value))) : $query->where($column, $value); break;
            case 'startsWith': $query->where($column, 'like', $value . '%'); break;
            case 'contains': $query->where($column, 'like', '%' . $value . '%'); break;
            case 'notContains': $query->where($column, 'not like', '%' . $value . '%'); break;
            case 'endsWith': $query->where($column, 'like', '%' . $value); break;
            case 'notEquals': $query->where($column, '!=', $value); break;
            case 'greaterThan': $query->where($column, '>', $value); break;
            case 'greaterThanOrEqualTo': $query->where($column, '>=', $value); break;
            case 'lessThan': $query->where($column, '<', $value); break;
            case 'lessThanOrEqualTo': $query->where($column, '<=', $value); break;
            case 'in': $query->whereIn($column, is_array($value) ? array_values(array_unique($value)) : [$value]); break;
            case 'between':
                if (is_array($value) && count($value) === 2) {
                    $query->whereBetween($column, [$value[0], $value[1]]);
                }
                break;
            case 'dateIs': $query->whereDate($column, '=', $value); break;
            case 'dateIsNot': $query->whereDate($column, '!=', $value); break;
            case 'dateBefore': $query->whereDate($column, '<', $value); break;
            case 'dateAfter': $query->whereDate($column, '>', $value); break;
        }
    }
}
