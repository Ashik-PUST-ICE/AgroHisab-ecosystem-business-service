<?php

namespace App\Http\Resources\Business\AuditLog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'action'         => $this->action,
            'auditable_type' => $this->auditable_type,
            'auditable_id'   => $this->auditable_id,
            'description'    => $this->description,
            'user_id'        => $this->user_id,
            'ip_address'     => $this->ip_address,
            'payload'        => $this->payload,
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}