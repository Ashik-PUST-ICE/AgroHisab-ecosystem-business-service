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
            'file'              => ['required', 'file', 'max:10240', 'mimes:jpeg,png,jpg,webp,pdf,doc,docx'],
            'title'             => ['required', 'string', 'max:255'],
            'document_type'     => ['nullable', 'string', 'in:memo,challan,voucher,receipt,agreement,other'],
            'documentable_type' => ['nullable', 'string', 'max:255'],
            'documentable_id'   => ['nullable', 'integer'],
            'notes'             => ['nullable', 'string', 'max:1000'],
        ];
    }
}