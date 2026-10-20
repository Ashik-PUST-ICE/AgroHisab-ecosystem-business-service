<?php

namespace App\Http\Requests\Business\Document;

use Illuminate\Foundation\Http\FormRequest;

class DocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file_path'         => ['required', 'string', 'max:500'],
            'title'             => ['required', 'string', 'max:255'],
            'document_type'     => ['nullable', 'string', 'in:memo,challan,voucher,receipt,agreement,other'],
            'file_name'         => ['nullable', 'string', 'max:255'],
            'mime_type'         => ['nullable', 'string', 'max:100'],
            'file_size'         => ['nullable', 'integer'],
            'documentable_type' => ['nullable', 'string', 'max:255'],
            'documentable_id'   => ['nullable', 'integer'],
            'notes'             => ['nullable', 'string', 'max:1000'],
        ];
    }
}