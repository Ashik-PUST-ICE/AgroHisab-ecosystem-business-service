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

    /**
     * Store a document.
     *
     * Mode A – Raw file upload (UploadedFile): proxied through FileManagerService
     *          → auth-service /api/v1/upload-files, with local public disk fallback.
     *
     * Mode B – Pre-uploaded (file = null): $data must include 'file_path'
     *          (frontend already uploaded to auth-service independently).
     */
    public function store(array $data, ?UploadedFile $file = null): Document
    {
        if ($file) {
            // Mode A: proxy upload through FileManagerService
            $fileData = $this->fileManager->upload($file, 'uploads/documents');
            $filePath = $fileData['path'];
            $fileName = $fileData['filename'] ?? $file->getClientOriginalName();
            $mimeType = $fileData['mime'] ?? $file->getClientMimeType();
            $fileSize = $fileData['size'] ?? $file->getSize();
        } else {
            // Mode B: path already resolved by frontend + auth-service
            $filePath = $data['file_path'];
            $fileName = $data['file_name'] ?? basename($filePath);
            $mimeType = $data['mime_type'] ?? null;
            $fileSize = $data['file_size'] ?? null;
        }

        return Document::create([
            'title'             => $data['title'],
            'document_type'     => $data['document_type'] ?? 'memo',
            'file_path'         => $filePath,
            'file_name'         => $fileName,
            'mime_type'         => $mimeType,
            'file_size'         => $fileSize,
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