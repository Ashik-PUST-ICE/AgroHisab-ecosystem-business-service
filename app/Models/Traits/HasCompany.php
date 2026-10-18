<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasCompany
{
    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function (Model $model) {
            if (! $model->isFillable('company_id') && ! in_array('company_id', $model->getFillable())) {
                return;
            }

            $companyId = request()->attributes->get('jwt_claims')['company_id'] ?? null;

            if ($companyId && empty($model->company_id)) {
                $model->company_id = $companyId;
            }
        });
    }
}
