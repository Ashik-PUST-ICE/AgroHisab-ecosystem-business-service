<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Aratdar extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'commission_type'  => 'integer',
            'commission_value' => 'decimal:4',
            'status'           => 'integer',
            'agreement_start'  => 'date',
            'agreement_end'    => 'date',
        ];
    }
}
