<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
        ];
    }
}
