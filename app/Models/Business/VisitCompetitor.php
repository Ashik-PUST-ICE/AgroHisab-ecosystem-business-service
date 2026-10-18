<?php

namespace App\Models\Business;

use App\Models\Traits\HasCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitCompetitor extends Model
{
    use HasCompany;

    protected $guarded = [];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function competitor(): BelongsTo
    {
        return $this->belongsTo(Competitor::class);
    }
}
