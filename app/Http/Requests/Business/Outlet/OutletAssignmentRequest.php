<?php

namespace App\Http\Requests\Business\Outlet;

use App\Models\Business\OutletAssignment;
use App\Services\Applications\Caches\UserCacheService;
use Illuminate\Foundation\Http\FormRequest;

class OutletAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('assignment') instanceof OutletAssignment;

        return [
            'outlet_id' => [$isUpdate ? 'sometimes' : 'required', 'exists:outlets,id'],
            // Users live in the AUTH service, not in this database. `exists:users,id`
            // made Laravel query `businessservice.users`, which does not exist, and
            // every assignment failed with SQLSTATE[42S02] (Table not found).
            // UserCacheService already proxies + caches auth users, so validate
            // through it instead of a local table lookup.
            'user_id' => [$isUpdate ? 'sometimes' : 'required', 'integer', $this->userExistsRule()],
            'status' => ['nullable', 'in:0,1'],
        ];
    }

    /**
     * Build the "the officer must exist in the auth service" rule.
     */
    private function userExistsRule(): \Closure
    {
        return function (string $attribute, $value, \Closure $fail): void {
            if (app(UserCacheService::class)->getUser($value) === null) {
                $fail('The selected field officer does not exist.');
            }
        };
    }
}
