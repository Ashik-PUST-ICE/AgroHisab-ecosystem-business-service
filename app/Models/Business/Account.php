<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'type'            => 'integer',
            'opening_balance' => 'decimal:2',
            'status'          => 'integer',
        ];
    }
}
