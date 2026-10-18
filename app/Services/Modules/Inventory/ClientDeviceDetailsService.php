<?php

namespace App\Services\Modules\Inventory;

use App\Models\ClientDeviceDetails;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ClientDeviceDetailsService
{
    protected $model;

    public function __construct()
    {
        $this->model = new ClientDeviceDetails;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        $query->when(isset($filters['user_id']), function ($q) use ($filters) {
            $q->where('user_id', $filters['user_id']);
        })->when(isset($filters['network_id']), function ($q) use ($filters) {
            $q->where('network_id', $filters['network_id']);
        })->when(isset($filters['model']), function ($q) use ($filters) {
            $q->where('model', $filters['model']);
        })->when(isset($filters['brand']), function ($q) use ($filters) {
            $q->where('brand', $filters['brand']);
        })->when(isset($filters['username']), function ($q) use ($filters) {
            $q->where('username', $filters['username']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): ClientDeviceDetails
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function update(array $attributes): ClientDeviceDetails
    {

        return DB::transaction(function () use ($attributes) {

            $device = $this->model->updateOrCreate(
                [
                    'user_id' => $attributes['user_id'],
                    'network_id' => $attributes['network_id'],
                ],
                [
                    'model' => $attributes['model'] ?? null,
                    'brand' => $attributes['brand'] ?? null,
                    'username' => $attributes['username'] ?? null,
                    'password' => $attributes['password'],
                    'created_by' => authId(),
                ]
            );

            log_activity('Created a new Client Device Details', authId(), null, 'client_device_details', 'created', [
                'id' => $device->id,
                'user_id' => $device->user_id,
                'network_id' => $device->network_id,
                'username' => $device->username,
            ]);

            return $device;
        });
    }
}
