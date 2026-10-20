<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessDiary extends Model
{
    use SoftDeletes;

    protected $table = 'business_diary';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'event_date' => 'date:Y-m-d',
        ];
    }

    public function related(): MorphTo
    {
        return $this->morphTo('related');
    }
}