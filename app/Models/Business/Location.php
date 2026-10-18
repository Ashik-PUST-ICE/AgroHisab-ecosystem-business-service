<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $table = 'locations';

    protected $fillable = [
        'parent_id',
        'name',
        'name_bn',
        'type',
        'code',
        'lat',
        'lng',
        'sort_order',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    /**
     * Every level of the administrative hierarchy lives in this one table, so
     * a single lookup query powers each dropdown in the outlet form.
     *
     * Union and pourashava sit at the same depth: rural areas use unions,
     * towns and cities use pourashavas. Both hang off an upazila and both
     * own wards, so the parent mapping below treats them as siblings.
     */
    public const TYPES = [
        'division',
        'district',
        'upazila',
        'union',
        'pourashava',
        'ward',
        'village',
    ];

    /**
     * The level a row of this type must hang off, or null when it is a root.
     *
     * Keeps the tree honest: a village belongs to a ward, never to an upazila.
     * Pourashava mirrors union, so ward accepts either as its parent.
     */
    public static function parentTypeFor(string $type): ?string
    {
        if ($type === 'division') {
            return null;
        }

        if ($type === 'pourashava') {
            return 'upazila'; // Sibling of union: towns hang off upazilas too.
        }

        if ($type === 'ward') {
            return 'union'; // See parentTypesFor(); pourashava also accepted.
        }

        $index = array_search($type, self::TYPES, true);

        if ($index === false || $index === 0) {
            return null;
        }

        return self::TYPES[$index - 1];
    }

    /**
     * Every parent type a row of this type may hang off.
     *
     * Ward is the only level with two legal parents: union (rural) and
     * pourashava (urban). Every other level has exactly one parent.
     *
     * @return string[]
     */
    public static function parentTypesFor(string $type): array
    {
        if ($type === 'ward') {
            return ['union', 'pourashava'];
        }

        $parent = self::parentTypeFor($type);

        return $parent === null ? [] : [$parent];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }
}