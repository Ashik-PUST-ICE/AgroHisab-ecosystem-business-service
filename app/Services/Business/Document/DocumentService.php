<?php

namespace App\Services\Business\Document;

use App\Models\Business\Document;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

class DocumentService
{
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
        $extension = $file->getClientOriginalExtension();
        $originalName = $file->getClientOriginalName();
        $fileName = 'doc_' . Carbon::now()->format('Ymd_His') . '_' . uniqid() . '.' . $extension;

        $targetDir = public_path('uploads/documents');
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $file->move($targetDir, $fileName);
        $filePath = 'uploads/documents/' . $fileName;

        return Document::create([
            'title'             => $data['title'],
            'document_type'     => $data['document_type'] ?? 'memo',
            'file_path'         => $filePath,
            'file_name'         => $originalName,
            'mime_type'         => $file->getClientMimeType(),
            'file_size'         => file_exists(public_path($filePath)) ? filesize(public_path($filePath)) : 0,
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
        $fullPath = public_path($document->file_path);
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }

        $document->delete();
    }
}