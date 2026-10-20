<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AratdarRemittance extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'payment_date'       => 'date:Y-m-d',
            'amount'             => 'decimal:2',
            'allocated_amount'   => 'decimal:2',
            'unallocated_amount' => 'decimal:2',
        ];
    }

    public function aratdar(): BelongsTo
    {
        return $this->belongsTo(Aratdar::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(RemittanceAllocation::class, 'aratdar_remittance_id');
    }
}