<?php

namespace App\DTOs\Applications;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use JsonSerializable;

final class LogPayload implements Arrayable, JsonSerializable
{
    public function __construct(
        public string $message,
        public ?string $targetId = null,
        public array $context = [],
        public ?string $actorId = null,
        public ?string $category = null,
        public ?string $action = null,
        public ?string $ip = null,
        public ?string $userAgent = null,
        public string $level = 'info',
        public ?string $logId = null,
        public ?string $occurredAt = null,
        public ?string $service = null,
        public ?string $env = null,
        public ?string $requestId = null,
        public ?string $traceId = null,
    ) {
        $this->occurredAt = $this->occurredAt ?? now()->toIso8601String();
        $this->service = $this->service ?? config('app.name');
        $this->env = $this->env ?? app()->environment();
        $this->requestId = $this->requestId ?? (string) Str::uuid();
    }

    public static function from(
        string $message,
        ?string $targetId = null,
        array $context = [],
        ?string $actorId = null,
        ?string $category = null,
        ?string $action = null,
        ?string $ip = null,
        ?string $userAgent = null,
        ?string $level = 'info',
        ?string $logId = null,
        ?string $requestId = null,
        ?string $traceId = null,
    ): self {
        return new self(
            message: $message,
            targetId: $targetId ?? data_get($context, 'user_id') ?? data_get($context, 'userId'),
            context: $context,
            actorId: $actorId ?? authId() ?? auth()->id() ?? null,
            category: $category ?? data_get($context, 'category'),
            action: $action ?? data_get($context, 'action'),
            ip: $ip,
            userAgent: $userAgent,
            level: $level ?? 'info',
            logId: $logId,
            requestId: $requestId,
            traceId: $traceId,
        );
    }

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'target_id' => $this->targetId,
            'context' => $this->context,
            'actor_id' => $this->actorId,
            'category' => $this->category,
            'action' => $this->action,
            'ip' => $this->ip,
            'user_agent' => $this->userAgent,
            'level' => $this->level,
            'log_id' => $this->logId,
            'timestamp' => $this->occurredAt,
            'service' => $this->service,
            'env' => $this->env,
            'request_id' => $this->requestId,
            'trace_id' => $this->traceId,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
