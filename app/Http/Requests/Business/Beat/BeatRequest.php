<?php

namespace App\Http\Requests\Business\Beat;

use App\Models\Business\Beat;
use App\Services\Applications\Caches\UserCacheService;
use Illuminate\Foundation\Http\FormRequest;

class BeatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('beat') instanceof Beat;

        return [
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            // Same bug as OutletAssignmentRequest: `users` lives in the auth
            // service, so `exists:users,id` queried a table that is not in this
            // database and threw SQLSTATE[42S02].
            'assigned_user_id' => [
                'nullable',
                'integer',
                function (string $attribute, $value, \Closure $fail): void {
                    if (app(UserCacheService::class)->getUser($value) === null) {
                        $fail('The selected officer does not exist.');
                    }
                },
            ],
            'date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:0,1'],
        ];
    }
}
