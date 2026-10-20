<?php

namespace App\Http\Controllers\Api\V1\Business\System;

use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\System\BackupService;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function __construct(protected BackupService $backupService) {}

    public function index()
    {
        return $this->handleRequest(function () {
            $backups = $this->backupService->listBackups();

            return ApiResponse::success($backups, 'System backups retrieved successfully');
        });
    }

    public function store()
    {
        return $this->handleRequest(function () {
            $backup = $this->backupService->createBackup();

            return ApiResponse::success($backup, 'Database ledger backup created successfully', 201);
        });
    }
}