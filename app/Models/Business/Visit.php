<?php

namespace App\Models\Business;

use App\Models\Traits\HasCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visit extends Model
{
    use HasCompany;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'verification_status' => 'boolean',
            'distance_meters' => 'integer',
            'allowed_radius_meters' => 'integer',
            'display_quantity' => 'integer',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function beat(): BelongsTo
    {
        return $this->belongsTo(Beat::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(VisitPhoto::class);
    }

    public function competitors(): HasMany
    {
        return $this->hasMany(VisitCompetitor::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(VisitProduct::class);
    }
}
