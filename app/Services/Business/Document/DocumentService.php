<?php

namespace App\Services\Business\Document;

use App\Models\Business\Document;
use App\Services\Applications\FileManager\FileManagerService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

class DocumentService
{
    public function __construct(
        protected FileManagerService $fileManager
    ) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Document::query();

        if (!empty($filters['documentable_type'])) {
            $query->where('documentable_type', $filters['documentable_type']);
        }

        if (!empty($filters['documentable_id'])) {
            $query->where('documentable_id', $filters['documentable_id']);
        }

        if (!empty($filters['document_type'])) {
            $query->where('document_type', $filters['document_type']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        $perPage = (int) ($filters['per_page'] ?? 15);
        return $query->latest('id')->paginate($perPage);
    }

    public function store(array $data, UploadedFile $file): Document
    {
        $fileData = $this->fileManager->upload($file, 'uploads/documents');

        return Document::create([
            'title'             => $data['title'],
            'document_type'     => $data['document_type'] ?? 'memo',
            'file_path'         => $fileData['path'],
            'file_name'         => $fileData['filename'] ?? $file->getClientOriginalName(),
            'mime_type'         => $fileData['mime'] ?? $file->getClientMimeType(),
            'file_size'         => $fileData['size'] ?? $file->getSize(),
            'documentable_type' => $data['documentable_type'] ?? null,
            'documentable_id'   => $data['documentable_id'] ?? null,
            'notes'             => $data['notes'] ?? null,
            'created_by'        => authId(),
        ]);
    }

    public function show(Document $document): Document
    {
        return $document;
    }

    public function destroy(Document $document): void
    {
        $this->fileManager->delete($document->file_path);
        $document->delete();
    }
}