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
        // Mode A: client sends raw 'file' -> FileManagerService proxies upload to auth-service
        // Mode B: client sends 'file_path' (pre-uploaded via auth /api/v1/upload-files)
        $hasFile = $this->hasFile('file');

        return [
            'file'              => $hasFile ? ['required', 'file', 'max:20480'] : ['nullable'],
            'file_path'         => $hasFile ? ['nullable', 'string', 'max:500'] : ['required', 'string', 'max:500'],
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