<?php

use App\DTOs\Applications\LogPayload;
use App\Events\Applications\LogActivity;

if (! function_exists('log_activity')) {
    /**
     * Emit a log activity event (non-blocking).
     *
     * $context should be serializable (array).
     */
    function log_activity(
        string $message,
        ?string $actorId = null,
        ?string $targetId = null,
        ?string $category = null,
        ?string $action = null,
        array $context = [],
        ?string $level = 'info',
        ?string $ip = null,
        ?string $userAgent = null,
        ?string $logId = null
    ): void {
        $payload = LogPayload::from(
            message: $message,
            targetId: $targetId ?? data_get($context, 'user_id') ?? data_get($context, 'userId') ?? null,
            context: $context,
            actorId: $actorId ?? authId() ?? auth()->id() ?? null,
            category: $category ?? data_get($context, 'category') ?? null,
            action: $action ?? data_get($context, 'action') ?? null,
            ip: $ip ?? (app()->bound('request') ? request()->ip() : null),
            userAgent: $userAgent ?? (app()->bound('request') ? request()->userAgent() : null),
            level: $level,
            logId: $logId,
            requestId: app()->bound('request') ? request()->header(config('gateway.request_id_header', 'X-Request-ID')) : null,
            traceId: app()->bound('request') ? request()->header('X-Trace-ID') : null,
        );

        event(new LogActivity($payload));
    }
}
