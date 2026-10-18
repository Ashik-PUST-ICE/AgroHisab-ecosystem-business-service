<?php

namespace App\Services\Modules\Hrm;

use App\Models\ShiftAssignment;
use Illuminate\Pagination\LengthAwarePaginator;

class ShiftAssignmentService
{
    protected $model;

    public function __construct()
    {
        $this->model = new ShiftAssignment;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with('shift');

        $query->when(isset($filters['user_id']), function ($q) use ($filters) {
            $q->where('user_id', $filters['user_id']);
        })->when(isset($filters['shift_id']), function ($q) use ($filters) {
            $q->where('shift_id', $filters['shift_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->whereHas('user', function ($userQuery) use ($filters) {
                $userQuery->where('name', 'like', '%'.$filters['search'].'%');
            })->orWhereHas('shift', function ($shiftQuery) use ($filters) {
                $shiftQuery->where('title', 'like', '%'.$filters['search'].'%');
            });
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): ShiftAssignment
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): ShiftAssignment
    {
        $shiftAssignment = $this->model->create($attributes);

        log_activity('Created a new shift assignment', authId(), null, 'shift_assignment', 'created', [
            'id' => $shiftAssignment->id,
            'user_id' => $shiftAssignment->user_id,
            'shift_id' => $shiftAssignment->shift_id,
        ]);

        return $shiftAssignment;
    }

    public function update(string $id, array $attributes): ShiftAssignment
    {
        $shiftAssignment = $this->model->findOrFail($id);
        $shiftAssignment->update($attributes);

        log_activity('Updated shift assignment', authId(), null, 'shift_assignment', 'updated', $shiftAssignment->getChanges() + ['id' => $shiftAssignment->id]);

        return $shiftAssignment->fresh();
    }

    public function destroy(string $id): void
    {
        $shiftAssignment = $this->show($id);
        $shiftAssignment->delete();
        log_activity('Deleted a shift assignment', authId(), null, 'shift_assignment', 'deleted', $shiftAssignment->toArray());
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['user_id']), function ($q) use ($filters) {
            $q->where('user_id', $filters['user_id']);
        })->when(isset($filters['shift_id']), function ($q) use ($filters) {
            $q->where('shift_id', $filters['shift_id']);
        })->when(isset($filters['search']), function ($q) use ($filters) {
            $q->whereHas('user', function ($userQuery) use ($filters) {
                $userQuery->where('name', 'like', '%'.$filters['search'].'%');
            })->orWhereHas('shift', function ($shiftQuery) use ($filters) {
                $shiftQuery->where('title', 'like', '%'.$filters['search'].'%');
            });
        });

        $limit = $filters['limit'] ?? 10;

        return $query->with('shift:id,title')->latest()->limit($limit)->get();

    }
}
