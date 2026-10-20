<?php

namespace App\Http\Controllers\Api\V1\Business\Document;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Document\DocumentRequest;
use App\Http\Resources\Business\Document\DocumentResource;
use App\Models\Business\Document;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Document\DocumentService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(protected DocumentService $documentService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $documents = $this->documentService->list(
                $request->only(['documentable_type', 'documentable_id', 'document_type', 'search', 'per_page'])
            );
            return ApiResponse::success(DocumentResource::collection($documents), 'Documents retrieved successfully');
        });
    }

    public function store(DocumentRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            // Mode A: raw file present → FileManagerService proxies to auth-service
            // Mode B: no file → validated data contains file_path (pre-uploaded by frontend)
            $file     = $request->hasFile('file') ? $request->file('file') : null;
            $document = $this->documentService->store($request->validated(), $file);
            return ApiResponse::success(new DocumentResource($document), 'Document attached successfully', 201);
        });
    }

    public function show(Document $document)
    {
        return $this->handleRequest(function () use ($document) {
            $doc = $this->documentService->show($document);
            return ApiResponse::success(new DocumentResource($doc), 'Document retrieved successfully');
        });
    }

    public function destroy(Document $document)
    {
        return $this->handleRequest(function () use ($document) {
            $this->documentService->destroy($document);
            return ApiResponse::success(null, 'Document detached successfully');
        });
    }
}