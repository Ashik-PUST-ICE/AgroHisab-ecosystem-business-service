<?php

namespace App\Services\Business\Beat;

use App\Models\Business\Beat;
use App\Models\Business\BeatOutlet;

class BeatOutletService
{
    public function index(Beat $beat)
    {
        return $beat->outlets()->with('outlet')->get();
    }

    public function store(Beat $beat, array $data): BeatOutlet
    {
        return BeatOutlet::create([
            'beat_id' => $beat->id,
            'company_id' => $beat->company_id,
            'outlet_id' => $data['outlet_id'],
            'sequence' => $data['sequence'] ?? 0,
            'status' => $data['status'] ?? true,
        ]);
    }

    public function destroy(Beat $beat, BeatOutlet $beatOutlet): void
    {
        if ($beatOutlet->beat_id !== $beat->id) {
            return;
        }

        $beatOutlet->delete();
    }
}
