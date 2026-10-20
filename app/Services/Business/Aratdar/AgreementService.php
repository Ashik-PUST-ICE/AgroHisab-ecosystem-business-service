<?php

namespace App\Services\Business\Aratdar;

use App\Models\Business\Aratdar;
use Carbon\Carbon;

class AgreementService
{
    public function show(Aratdar $aratdar): array
    {
        return $this->format($aratdar);
    }

    public function update(Aratdar $aratdar, array $data): array
    {
        $aratdar->update([
            'commission_type'  => $data['commission_type'],
            'commission_value' => $data['commission_value'],
            'agreement_start'  => Carbon::parse($data['agreement_start'])->toDateString(),
            'agreement_end'    => !empty($data['agreement_end'])
                ? Carbon::parse($data['agreement_end'])->toDateString()
                : null,
        ]);

        return $this->format($aratdar->fresh());
    }

    private function format(Aratdar $aratdar): array
    {
        $now = Carbon::now();
        return [
            'aratdar_id'       => $aratdar->id,
            'aratdar_name'     => $aratdar->name,
            'commission_type'  => $aratdar->commission_type,
            'commission_label' => $aratdar->commission_type === 2
                ? 'Fixed Per Bag (৳)'
                : 'Percentage (%)',
            'commission_value' => (float) ($aratdar->commission_value ?? 0),
            'agreement_start'  => $aratdar->agreement_start?->toDateString(),
            'agreement_end'    => $aratdar->agreement_end?->toDateString(),
            'is_active'        => !$aratdar->agreement_end
                || $now->lte($aratdar->agreement_end),
        ];
    }
}