<?php

namespace App\Http\Requests\Business;

use App\Models\Business\Location;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class LocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $location = $this->route('location');
        $isUpdate = $location instanceof Location;

        return [
            'name' => ['required', 'string', 'max:120'],
            'name_bn' => ['nullable', 'string', 'max:120'],
            'type' => ['required', 'string', 'in:'.implode(',', Location::TYPES)],
            'parent_id' => ['nullable', 'integer', 'exists:locations,id'],
            'code' => [
                'nullable',
                'string',
                'max:20',
                // Two seeded rows must never share an official code.
                Rule::unique('locations', 'code')->ignore($location?->id),
            ],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'parent_id.exists' => 'The selected parent location does not exist.',
            'type.in' => 'Unknown location level.',
        ];
    }

    /**
     * Guards the shape of the tree.
     *
     * A village has to hang off a ward, not straight off an upazila, and a
     * division cannot have a parent at all. Without this the dropdown would
     * happily offer levels that no longer line up.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $type = $this->input('type');

            if (! in_array($type, Location::TYPES, true)) {
                return;
            }

            $expectedParents = Location::parentTypesFor($type);

            if ($expectedParents === []) {
                if ($this->filled('parent_id')) {
                    $validator->errors()->add('parent_id', "A {$type} is a top level and cannot have a parent.");
                }

                return;
            }

            if (! $this->filled('parent_id')) {
                $validator->errors()->add('parent_id', "A {$type} must be placed under a {$expectedParents[0]}.");

                return;
            }

            $parent = Location::find($this->input('parent_id'));

            if ($parent && ! in_array($parent->type, $expectedParents, true)) {
                $allowed = implode(' or ', $expectedParents);
                $validator->errors()->add(
                    'parent_id',
                    "A {$type} must be placed under a {$allowed}, not a {$parent->type}."
                );
            }
        });
    }
}