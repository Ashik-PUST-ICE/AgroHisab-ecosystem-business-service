<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShipmentSale extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'sale_date'         => 'date:Y-m-d',
            'quantity'          => 'decimal:4',
            'unit_rate'         => 'decimal:2',
            'gross_amount'      => 'decimal:2',
            'discount_amount'   => 'decimal:2',
            'net_amount'        => 'decimal:2',
            'commission_type'   => 'integer',
            'commission_rate'   => 'decimal:4',
            'commission_amount' => 'decimal:2',
            'net_proceeds'      => 'decimal:2',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function shipmentItem(): BelongsTo
    {
        return $this->belongsTo(ShipmentItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }
}