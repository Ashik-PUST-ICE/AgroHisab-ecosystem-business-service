<?php

namespace App\Services\Applications\FileManager;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileManagerService
{
    protected string $authBase;

    public function __construct()
    {
        $this->authBase = rtrim(env('AUTH_SERVICE_MAIN_URI', 'http://127.0.0.1:8000'), '/');
    }

    /**
     * Upload file using Auth Service file upload endpoint, with local public disk fallback.
     */
    public function upload(UploadedFile $file, string $dir = 'uploads/files'): array
    {
        $token = request()->bearerToken();

        // 1. Try uploading to Auth Service's file upload system
        if ($this->authBase && $token) {
            try {
                $response = Http::timeout(10)
                    ->withToken($token)
                    ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                    ->post("{$this->authBase}/api/v1/upload-files");

                if ($response->successful()) {
                    $json = $response->json();
                    $data = $json['data'] ?? [];
                    if (!empty($data['path'])) {
                        return [
                            'path'     => $data['path'],
                            'url'      => $data['url'] ?? assetUrl($data['path']),
                            'filename' => $data['filename'] ?? $file->getClientOriginalName(),
                            'mime'     => $data['mime'] ?? $file->getClientMimeType(),
                            'size'     => (int) ($data['size'] ?? $file->getSize()),
                        ];
                    }
                }

                Log::warning("FileManagerService: Auth service upload failed with status " . $response->status());
            } catch (\Throwable $e) {
                Log::warning("FileManagerService: Could not connect to Auth Service upload endpoint: " . $e->getMessage());
            }
        }

        // 2. Local Storage fallback (public disk)
        $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $basename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file';
        $filename = $basename . '_' . Carbon::now()->format('Ymd_His') . '_' . Str::random(6) . '.' . $ext;
        $diskPath = trim($dir, '/') . '/' . $filename;

        Storage::disk('public')->putFileAs(trim($dir, '/'), $file, $filename);

        return [
            'path'     => $diskPath,
            'url'      => assetUrl($diskPath),
            'filename' => $filename,
            'mime'     => $file->getClientMimeType() ?: 'application/octet-stream',
            'size'     => $file->getSize() ?: 0,
        ];
    }

    /**
     * Delete file using Auth Service or local disk.
     */
    public function delete(string $filePath): void
    {
        $token = request()->bearerToken();

        if ($this->authBase && $token) {
            try {
                $response = Http::timeout(6)
                    ->withToken($token)
                    ->post("{$this->authBase}/api/v1/delete-files", [
                        'file_path' => $filePath,
                    ]);

                if ($response->successful()) {
                    return;
                }
            } catch (\Throwable $e) {
                Log::warning("FileManagerService: Auth service delete error: " . $e->getMessage());
            }
        }

        // Local fallback delete
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
    }
}