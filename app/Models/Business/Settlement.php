<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Settlement extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'settlement_date'       => 'date:Y-m-d',
            'gross_sales'           => 'decimal:2',
            'sale_discounts'        => 'decimal:2',
            'net_sales'             => 'decimal:2',
            'commission_amount'     => 'decimal:2',
            'owner_freight'         => 'decimal:2',
            'aratdar_freight'       => 'decimal:2',
            'other_deductions'      => 'decimal:2',
            'expected_net_proceeds' => 'decimal:2',
            'remitted_amount'       => 'decimal:2',
            'outstanding_balance'   => 'decimal:2',
            'allocated_cogs'        => 'decimal:2',
            'realized_profit'       => 'decimal:2',
            'status'                => 'integer',
            'approved_at'           => 'datetime',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function aratdar(): BelongsTo
    {
        return $this->belongsTo(Aratdar::class);
    }
}