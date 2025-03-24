<?php

namespace App\Traits\Models;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    public function scopeFilter(Builder $query, ?string $filters): void
    {
        $query->when($filters, function (Builder $query) use ($filters) {
            $filters = json_decode($filters, true);
            foreach ($filters as $column => $filter) {
                $query->when(!isset($filter['operator']) || !isset($filter['constraints']), function (Builder $query) use ($filter, $column) {
                    $value = $filter['value'];
                    $matchMode = $filter['matchMode'];
                    $this->applyFilter($query, $column, $matchMode, $value);
                }, function (Builder $query) use ($filter, $column) {
                    $operator = strtolower($filter['operator']);
                    $constraints = $filter['constraints'];
                    $query->where(function (Builder $q) use ($column, $constraints, $operator) {
                        foreach ($constraints as $index => $constraint) {
                            $value = $constraint['value'];
                            $matchMode = $constraint['matchMode'];

                            if ($index === 0) {
                                $this->applyFilter($q, $column, $matchMode, $value);
                            } else {
                                if ($operator === 'or') {
                                    $q->orWhere(fn($subQuery) => $this->applyFilter($subQuery, $column, $matchMode, $value));
                                } else {
                                    $q->where(fn($subQuery) => $this->applyFilter($subQuery, $column, $matchMode, $value));
                                }
                            }
                        }
                    });
                });
            }
        });
    }

    private function applyFilter($query, $column, $matchMode, $value): void
    {
        switch ($matchMode) {
            case 'equals':
                if ($value === null) return;
                $query->where($column, $value);
                break;
            case 'startsWith': $query->where($column, 'like', $value . '%'); break;
            case 'contains': $query->where($column, 'like', '%' . $value . '%'); break;
            case 'endsWith': $query->where($column, 'like', '%' . $value); break;
            case 'notEquals': $query->where($column, '!=', $value); break;
            case 'greaterThan': $query->where($column, '>', $value); break;
            case 'greaterThanOrEqual': $query->where($column, '>=', $value); break;
            case 'lessThan': $query->where($column, '<', $value); break;
            case 'lessThanOrEqual': $query->where($column, '<=', $value); break;
        }
    }
}
