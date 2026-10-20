<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceObservation extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'observed_date' => 'date:Y-m-d',
            'min_price'     => 'decimal:2',
            'max_price'     => 'decimal:2',
            'avg_price'     => 'decimal:2',
        ];
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}