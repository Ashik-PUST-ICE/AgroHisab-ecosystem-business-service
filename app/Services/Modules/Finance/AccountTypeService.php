<?php

namespace App\Services\Modules\Finance;

use App\Enums\Commons\Status\StatusEnum;
use App\Models\AccountType;
use Illuminate\Pagination\LengthAwarePaginator;

class AccountTypeService
{
    protected AccountType $model;

    public function __construct()
    {
        $this->model = new AccountType;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $query->where('title', 'like', '%'.$filters['search'].'%');
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $data): AccountType
    {
        $AccountType = $this->model->create($data);
        log_activity('Created a new AccountType', authId(), null, 'account_type', 'created', [
            'id' => $AccountType->id,
            'title' => $AccountType->title,
        ]);

        return $AccountType;
    }

    public function show(string $id, $relations = [], $throwException = true): AccountType
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function update(string $id, array $data): AccountType
    {
        $AccountType = $this->show($id);
        $AccountType->update($data);
        log_activity('Updated a AccountType', authId(), null, 'account_type', 'updated', $AccountType->getChanges() + ['id' => $AccountType->id]);

        return $AccountType;
    }

    public function destroy(string $id): void
    {
        $AccountType = $this->show($id);
        $AccountType->delete();
        log_activity('Deleted a AccountType', authId(), null, 'account_type', 'deleted', $AccountType->toArray());
    }

    public function toggleStatus(string $id): AccountType
    {
        $AccountType = $this->show($id);
        $AccountType->status = $AccountType->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $AccountType->save();
        log_activity('Toggled AccountType status to '.($AccountType->status->label()), authId(), null, 'account_type', 'updated', $AccountType->getChanges() + ['id' => $AccountType->id]);

        return $AccountType;
    }
}
