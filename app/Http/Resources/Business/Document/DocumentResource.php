<?php

namespace App\Http\Resources\Business\Document;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'document_type'     => $this->document_type,
            'file_url'          => url($this->file_path),
            'file_name'         => $this->file_name,
            'mime_type'         => $this->mime_type,
            'file_size'         => (int) $this->file_size,
            'documentable_type' => $this->documentable_type,
            'documentable_id'   => $this->documentable_id,
            'notes'             => $this->notes,
            'created_at'        => $this->created_at?->toIso8601String(),
        ];
    }
}