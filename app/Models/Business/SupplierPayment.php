<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPayment extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'payment_date' => 'date:Y-m-d',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}