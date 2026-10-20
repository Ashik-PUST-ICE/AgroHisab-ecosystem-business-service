<?php

namespace App\Services\Business\AuditLog;

use App\Models\Business\AuditLog;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuditLogService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = AuditLog::query();

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['auditable_type'])) {
            $query->where('auditable_type', $filters['auditable_type']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', Carbon::parse($filters['from_date'])->toDateString());
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', Carbon::parse($filters['to_date'])->toDateString());
        }

        $perPage = (int) ($filters['per_page'] ?? 20);
        return $query->latest('id')->paginate($perPage);
    }

    public static function log(string $action, string $description, ?string $type = null, ?int $id = null, ?array $payload = null): void
    {
        try {
            AuditLog::create([
                'action'         => $action,
                'description'    => $description,
                'auditable_type' => $type,
                'auditable_id'   => $id,
                'user_id'        => authId(),
                'ip_address'     => request()->ip(),
                'user_agent'     => request()->userAgent(),
                'payload'        => $payload,
            ]);
        } catch (\Throwable $e) {
            // Silently ignore to avoid failing main business transaction
        }
    }
}