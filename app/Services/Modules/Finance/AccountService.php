<?php

namespace App\Services\Modules\Finance;

use App\Enums\Commons\Status\StatusEnum;
use App\Models\Account;
use Illuminate\Pagination\LengthAwarePaginator;

class AccountService
{
    protected Account $model;

    public function __construct()
    {
        $this->model = new Account;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%'.$filters['search'].'%')
                    ->orWhere('account_holder_name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('account_no', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function store(array $data): Account
    {
        $Account = $this->model->create($data);
        log_activity('Created a new Account', authId(), null, 'account', 'created', [
            'id' => $Account->id,
            'title' => $Account->title,
        ]);

        return $Account;
    }

    public function show(string $id, $relations = [], $throwException = true): Account
    {
        $defaultRelations = ['centralAccount'];
        $allRelations = array_merge($defaultRelations, $relations);

        $query = $this->model->with($allRelations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function update(string $id, array $data): Account
    {
        $Account = $this->show($id);
        $Account->update($data);
        log_activity('Updated a Account', authId(), null, 'account', 'updated', $Account->getChanges() + ['id' => $Account->id]);

        return $Account;
    }

    public function destroy(string $id): void
    {
        $Account = $this->show($id);
        $Account->delete();
        log_activity('Deleted a Account', authId(), null, 'account', 'deleted', $Account->toArray());
    }

    public function status(string $id): Account
    {
        $Account = $this->show($id);
        $Account->status = $Account->status === StatusEnum::ACTIVE
            ? StatusEnum::INACTIVE
            : StatusEnum::ACTIVE;
        $Account->save();
        log_activity('Toggled Account status to '.($Account->status->label()), authId(), null, 'account', 'updated', $Account->getChanges() + ['id' => $Account->id]);

        return $Account;
    }

    public function list(array $filters): array
    {
        $query = $this->model->newQuery();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        $accounts = $query->latest()->get(['id', 'title', 'account_no']);

        return $accounts->map(function ($account) {
            return [
                'value' => $account->id,
                'label' => $account->title.' ('.$account->account_no.')',
            ];
        })->toArray();
    }
}
