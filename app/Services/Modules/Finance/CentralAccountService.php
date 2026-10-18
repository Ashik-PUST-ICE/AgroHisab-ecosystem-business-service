<?php

namespace App\Services\Modules\Finance;

use App\Enums\Commons\Status\StatusEnum;
use App\Models\CentralAccount;
use Illuminate\Pagination\LengthAwarePaginator;

class CentralAccountService
{
    protected CentralAccount $model;

    public function __construct()
    {
        $this->model = new CentralAccount;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $data): CentralAccount
    {
        $centralAccount = $this->model->create($data);
        log_activity('Created a new CentralAccount', authId(), null, 'central_account', 'created', [
            'id' => $centralAccount->id,
            'title' => $centralAccount->title,
        ]);

        return $centralAccount;
    }

    public function show(string $id, $relations = [], $throwException = true): CentralAccount
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function update(string $id, array $attributes): CentralAccount
    {
        $centralAccount = $this->show($id);
        $centralAccount->update($attributes);
        log_activity('Updated a CentralAccount', authId(), null, 'central_account', 'updated', $centralAccount->getChanges() + ['id' => $centralAccount->id]);

        return $centralAccount;
    }

    public function destroy(string $id): void
    {
        $centralAccount = $this->show($id);
        log_activity('Deleted a CentralAccount', authId(), null, 'central_account', 'deleted', $centralAccount->toArray());
        $centralAccount->delete();
    }

    public function toggleStatus(string $id): CentralAccount
    {
        $centralAccount = $this->show($id);
        $centralAccount->status = $centralAccount->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $centralAccount->save();
        log_activity('Toggled CentralAccount status to '.($centralAccount->status->label()), authId(), null, 'central_account', 'updated', $centralAccount->getChanges() + ['id' => $centralAccount->id]);

        return $centralAccount;
    }
}
