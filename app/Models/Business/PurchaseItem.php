<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'bag_count'       => 'integer',
            'actual_weight'   => 'decimal:4',
            'deducted_weight' => 'decimal:4',
            'gross_weight'    => 'decimal:4',
            'tare_weight'     => 'decimal:4',
            'net_weight'      => 'decimal:4',
            'mon_rate'        => 'decimal:2',
            'mon_size'        => 'decimal:2',
            'unit_rate'       => 'decimal:2',
            'line_total'      => 'decimal:2',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
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