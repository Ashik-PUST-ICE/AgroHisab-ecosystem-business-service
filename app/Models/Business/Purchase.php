<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'purchase_date'   => 'date:Y-m-d',
            'total_amount'    => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'paid_amount'     => 'decimal:2',
            'due_amount'      => 'decimal:2',
            'status'          => 'integer',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }
}