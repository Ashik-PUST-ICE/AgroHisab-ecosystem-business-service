<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountTransaction extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'amount'     => 'decimal:2',
            'txn_date'   => 'date:Y-m-d',
            'direction'  => 'integer',
            'type'       => 'integer',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function contraAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'contra_account_id');
    }
}