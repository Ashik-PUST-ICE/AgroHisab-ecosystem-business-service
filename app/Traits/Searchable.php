<?php

namespace App\Traits;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait Searchable
{
    /**
     * Scope a query to search across specified fields.
     *
     * @param  string|null  $term  Search term (empty/null returns original builder)
     * @param  array|null  $searchables  Fields to search (fallback to $this->searchable, $fillable)
     * @param  string  $operator  SQL operator (default: 'like')
     * @param  bool  $exact  Exact match (if true will use '=' for like operators)
     *
     * @throws Exception
     */
    public function scopeSearch(
        Builder $builder,
        ?string $term = null,
        ?array $searchables = null,
        string $operator = 'like',
        bool $exact = false
    ): Builder {
        $term = $term ?? '';

        // If term is empty/only spaces -> return builder unchanged
        if (trim((string) $term) === '') {
            return $builder;
        }

        $searchables = $searchables ?? $this->getSearchableAttributes();

        if (empty($searchables)) {
            throw new Exception('No searchable attributes defined. Set $searchable on the model or pass an array.');
        }

        // Normalize operator
        $operator = strtolower(trim($operator));
        $allowedOperators = ['=', '!=', '<>', '<', '>', '<=', '>=', 'like', 'not like', 'ilike'];
        if (! in_array($operator, $allowedOperators, true)) {
            throw new Exception(sprintf('Invalid operator "%s". Allowed: %s', $operator, implode(', ', $allowedOperators)));
        }

        // If exact flag is set and operator is a like-variant, switch to '=' (strict equality)
        if ($exact && in_array($operator, ['like', 'not like', 'ilike'], true)) {
            $operator = $operator === 'not like' ? '!=' : '=';
        }

        // If the term looks numeric and searching an *_id column, do exact compare by default.
        // We'll handle per-column logic when iterating searchables.
        $isTermNumeric = is_numeric($term);

        $builder->where(function (Builder $q) use ($searchables, $operator, $term, $isTermNumeric, $exact) {
            foreach ($searchables as $searchable) {
                // Relation search if dotted (relation.column or nested.relation.column)
                if (Str::contains($searchable, '.')) {
                    $this->applyRelationSearch($q, (string) $searchable, $operator, $term, $isTermNumeric, $exact);

                    continue;
                }

                // Column-level logic:
                // If column is an id field and term numeric -> use exact comparison
                if ($isTermNumeric && Str::endsWith($searchable, ['_id', 'id'])) {
                    $q->orWhere($searchable, '=', $term);

                    continue;
                }

                // Use LIKE for fuzzy searches unless operator is strict
                $operand = $operator;
                $value = $term;
                if (! $exact && in_array($operator, ['like', 'not like', 'ilike'], true)) {
                    $value = "%{$term}%";
                }

                $q->orWhere($searchable, $operand, $value);
            }
        });

        return $builder;
    }

    /**
     * Resolve searchable attributes for the model.
     *
     * Priority:
     * 1. protected array $searchable
     * 2. protected $fillable (if set)
     * 3. derive from getFillable() minus guarded (best effort)
     */
    protected function getSearchableAttributes(): array
    {
        if (property_exists($this, 'searchable') && is_array($this->searchable) && ! empty($this->searchable)) {
            return $this->searchable;
        }

        if (! empty($this->fillable)) {
            return $this->fillable;
        }

        // If guarded is not ['*'] try to infer fillable-like attributes
        if (! empty($this->guarded) && $this->guarded !== ['*']) {
            return array_values(array_diff($this->getFillable(), $this->guarded));
        }

        return [];
    }

    /**
     * Apply search logic to relationship path (supports nested relations).
     *
     * Example inputs:
     *  - "profile.city"    => relationPath: "profile", column: "city"
     *  - "a.b.c.name"      => relationPath: "a.b.c", column: "name"
     *
     * Uses orWhereRelation for single-level relations if available, otherwise falls back to orWhereHas.
     *
     * @param  string  $relationPathDotCol  dotted relation path with final segment as column
     */
    protected function applyRelationSearch(
        Builder $builder,
        string $relationPathDotCol,
        string $operator,
        string $term,
        bool $isTermNumeric,
        bool $exact
    ): void {
        $parts = explode('.', $relationPathDotCol);
        $column = array_pop($parts);
        $relationPath = implode('.', $parts);

        if ($relationPath === '') {
            // defensive fallback: treat as direct column
            $builder->orWhere($column, $operator, $term);

            return;
        }

        // Determine value and operator per column (similar logic as direct columns)
        $operand = $operator;
        $value = $term;
        if (! $exact && in_array($operator, ['like', 'not like', 'ilike'], true)) {
            $value = "%{$term}%";
        }
        if ($isTermNumeric && Str::endsWith($column, ['_id', 'id'])) {
            $operand = '=';
            $value = $term;
        }

        // If single-level relation (no nested '.') we can use orWhereRelation which may be optimized
        if (! Str::contains($relationPath, '.')) {
            // orWhereRelation signature: orWhereRelation(relation, column, operator, value)
            // Use it if available in this Laravel version; otherwise fallback to orWhereHas
            if (method_exists($builder, 'orWhereRelation')) {
                $builder->orWhereRelation($relationPath, $column, $operand, $value);

                return;
            }
        }

        // Fallback for nested relations or when orWhereRelation not available:
        $builder->orWhereHas($relationPath, function (Builder $q) use ($column, $operand, $value) {
            $q->where($column, $operand, $value);
        });
    }
}
/**
 * 🔹 Usage:
 *
 * use App\Traits\Searchable;
 *
 * class User extends Model
 * {
 *     use Searchable;
 *
 *     protected array $searchable = ['name', 'email', 'profile.city'];
 * }
 *
 * // Examples:
 * User::search('john')->get();
 * User::search('john', ['email'])->get();
 * User::search('123', ['profile.id'])->get();
 */
