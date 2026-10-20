<?php

namespace App\Http\Controllers\Api\V1\Business\AuditLog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Business\AuditLog\AuditLogResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\AuditLog\AuditLogService;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __construct(protected AuditLogService $auditLogService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $logs = $this->auditLogService->list(
                $request->only(['action', 'auditable_type', 'user_id', 'from_date', 'to_date', 'per_page'])
            );

            return ApiResponse::success(AuditLogResource::collection($logs), 'Audit logs retrieved successfully');
        });
    }
}