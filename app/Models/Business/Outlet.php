<?php

namespace App\Models\Business;

use App\Models\Traits\HasCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outlet extends Model
{
    use HasCompany;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'geofence_radius' => 'integer',
            'qr_generated_at' => 'datetime',
            'qr_deactivated_at' => 'datetime',
        ];
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function beatOutlets(): HasMany
    {
        return $this->hasMany(BeatOutlet::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(OutletAssignment::class);
    }
}
