<?php

namespace App\Services\Modules\Inventory;

use App\Models\WorkDone;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class WorkDoneService
{
    protected WorkDone $model;

    public function __construct()
    {
        $this->model = new WorkDone;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        $query->when(isset($filters['user_id']), function ($q) use ($filters) {
            $q->where('user_id', $filters['user_id']);
        })->when(isset($filters['network_id']), function ($q) use ($filters) {
            $q->where('network_id', $filters['network_id']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $attributes): WorkDone
    {

        return DB::transaction(function () use ($attributes) {

            $workDone = $this->model->updateOrCreate(
                [
                    'user_id' => $attributes['user_id'],
                    'network_id' => $attributes['network_id'],
                    'created_by' => authId(),
                ],

            );

            log_activity('Created a new Work Done', authId(), null, 'work_done', 'created', [
                'id' => $workDone->id,
                'user_id' => $workDone->user_id,
                'network_id' => $workDone->network_id,
            ]);

            return $workDone;
        });
    }
}
