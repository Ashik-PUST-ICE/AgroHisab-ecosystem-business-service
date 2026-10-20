<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RemittanceAllocation extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'allocation_date' => 'date:Y-m-d',
            'amount'          => 'decimal:2',
        ];
    }

    public function remittance(): BelongsTo
    {
        return $this->belongsTo(AratdarRemittance::class, 'aratdar_remittance_id');
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}