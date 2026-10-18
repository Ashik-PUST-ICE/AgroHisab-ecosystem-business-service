<?php

namespace App\Services\Business\AuditLog;

use App\Models\Business\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public function log(array $data): AuditLog
    {
        return AuditLog::create([
            'actor_id' => $data['actor_id'] ?? authId(),
            'target_id' => $data['target_id'] ?? null,
            'category' => $data['category'] ?? null,
            'action' => $data['action'] ?? null,
            'message' => $data['message'] ?? 'Activity logged',
            'context' => $data['context'] ?? [],
            'ip' => $data['ip'] ?? request()->ip(),
            'user_agent' => $data['user_agent'] ?? (request()->userAgent() ?: null),
            'level' => $data['level'] ?? 'info',
            'request_id' => $data['request_id'] ?? request()->header('X-Request-ID'),
            'trace_id' => $data['trace_id'] ?? request()->header('X-Trace-ID'),
        ]);
    }
}
