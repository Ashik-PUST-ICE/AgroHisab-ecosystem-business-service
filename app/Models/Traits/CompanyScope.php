<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $companyId = request()->attributes->get('jwt_claims')['company_id'] ?? null;

        if ($companyId) {
            $builder->where($model->getTable().'.company_id', $companyId);
        }
    }
}
