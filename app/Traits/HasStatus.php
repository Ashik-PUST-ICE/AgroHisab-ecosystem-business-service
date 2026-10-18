<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasStatus
{
    /**
     * Scope a query to filter by status.
     *
     * @param  string|array|null  $status
     */
    public function scopeStatus(Builder $query, int|string|array|null $status, string $column = 'status', string $operator = '='): Builder
    {
        if (is_null($status)) {
            return $query;
        }

        if (is_array($status)) {
            return $query->whereIn($column, $status);
        }

        return $query->where($column, $operator, $status);
    }

    /**
     * Shortcut for active status.
     */
    public function scopeActive(Builder $query, int|string|array|null $status = 1, string $column = 'status'): Builder
    {
        return $query->where($column, $status);
    }
}
