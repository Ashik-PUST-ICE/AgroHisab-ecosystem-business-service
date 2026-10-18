<?php

namespace App\Http\Resources\Business\Kpi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KpiTargetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'period_type' => $this->period_type,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'visit_target' => (int) $this->visit_target,
            'order_amount_target' => (float) $this->order_amount_target,
            'order_count_target' => (int) $this->order_count_target,
            'coverage_target_percentage' => (float) $this->coverage_target_percentage,
            'notes' => $this->notes,
            'status' => $this->status,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
