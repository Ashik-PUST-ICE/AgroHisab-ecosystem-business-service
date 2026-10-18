<?php

namespace App\Models\Business;

use App\Models\Traits\HasCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiTarget extends Model
{
    use HasCompany;

    protected $table = 'kpi_targets';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'visit_target' => 'integer',
            'order_amount_target' => 'decimal:2',
            'order_count_target' => 'integer',
            'coverage_target_percentage' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
