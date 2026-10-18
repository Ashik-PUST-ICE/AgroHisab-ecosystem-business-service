<?php

namespace App\Services\Modules\Hrm;

use App\Enums\Commons\Status\StatusEnum;
use App\Models\AttendenceDevice;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendenceDeviceService
{
    protected $model;

    public function __construct()
    {
        $this->model = new AttendenceDevice;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model;

        $query->when(isset($filters['branch_id']), function ($q) use ($filters) {
            $q->where('branch_id', $filters['branch_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('vendor', 'like', '%'.$filters['search'].'%')
                ->orWhere('model', 'like', '%'.$filters['search'].'%')
                ->orWhere('ip_address', 'like', '%'.$filters['search'].'%');
        })->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): AttendenceDevice
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): AttendenceDevice
    {
        $attendenceDevice = $this->model->create($attributes);

        log_activity('Created a new attendance device', authId(), null, 'attendence_device', 'created', [
            'id' => $attendenceDevice->id,
            'vendor' => $attendenceDevice->vendor,
            'model' => $attendenceDevice->model,
        ]);

        return $attendenceDevice;
    }

    public function update(string $id, array $attributes): AttendenceDevice
    {
        $attendenceDevice = $this->model->findOrFail($id);
        $attendenceDevice->update($attributes);

        log_activity('Updated attendance device', authId(), null, 'attendence_device', 'updated', $attendenceDevice->getChanges() + ['id' => $attendenceDevice->id]);

        return $attendenceDevice->fresh();
    }

    public function destroy(string $id): void
    {
        $attendenceDevice = $this->show($id);
        $attendenceDevice->delete();
        log_activity('Deleted a attendance device', authId(), null, 'attendence_device', 'deleted', $attendenceDevice->toArray());
    }

    public function status(string $id): AttendenceDevice
    {
        $attendenceDevice = $this->show($id);
        $attendenceDevice->status = $attendenceDevice->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $attendenceDevice->save();
        log_activity('Toggled attendance device status to '.($attendenceDevice->status->label()), authId(), null, 'attendence_device', 'updated', $attendenceDevice->getChanges() + ['id' => $attendenceDevice->id]);

        return $attendenceDevice;
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['branch_id']), function ($q) use ($filters) {
            $q->where('branch_id', $filters['branch_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->where('vendor', 'like', '%'.$filters['search'].'%')
                ->orWhere('model', 'like', '%'.$filters['search'].'%')
                ->orWhere('ip_address', 'like', '%'.$filters['search'].'%');
        });

        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'vendor', 'model', 'ip_address')->where('status', StatusEnum::ACTIVE)->latest()->limit($limit)->get();
    }
}
