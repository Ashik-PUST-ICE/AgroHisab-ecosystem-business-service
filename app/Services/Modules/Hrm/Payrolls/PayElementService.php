<?php

namespace App\Services\Modules\Hrm\Payrolls;

use App\Models\PayElement;
use Illuminate\Pagination\LengthAwarePaginator;

class PayElementService
{
    protected $model;

    public function __construct()
    {
        $this->model = new PayElement;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = $this->model->query();

        $query->when(isset($filters['element_type']), function ($q) use ($filters) {
            $q->where('element_type', $filters['element_type']);
        })->when(isset($filters['taxable']), function ($q) use ($filters) {
            $q->where('taxable', $filters['taxable']);
        })->when(isset($filters['calculation_type']), function ($q) use ($filters) {
            $q->where('calculation_type', $filters['calculation_type']);
        });

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function show(string $id, $relations = [], $throwException = true): PayElement
    {
        $query = $this->model->with($relations);
        if ($throwException) {
            return $query->findOrFail($id);
        }

        return $query->find($id);
    }

    public function store(array $attributes): PayElement
    {
        $attributes['created_by'] = authId();
        $payElement = $this->model->create($attributes);

        log_activity('Created a new pay element', authId(), null, 'pay_element', 'created', [
            'id' => $payElement->id,
            'code' => $payElement->code,
            'name' => $payElement->name,
            'element_type' => $payElement->element_type,
        ]);

        return $payElement;
    }

    public function update(string $id, array $attributes): PayElement
    {
        $payElement = $this->model->findOrFail($id);
        $attributes['created_by'] = authId();
        $payElement->update($attributes);

        log_activity('Updated pay element', authId(), null, 'pay_element', 'updated', $payElement->getChanges() + ['id' => $payElement->id]);

        return $payElement->fresh();
    }

    public function destroy(string $id): void
    {
        $payElement = $this->show($id);
        $payElement->delete();
        log_activity('Deleted a pay element', authId(), null, 'pay_element', 'deleted', $payElement->toArray());
    }

    public function list(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query->when(isset($filters['element_type']), function ($q) use ($filters) {
            $q->where('element_type', $filters['element_type']);
        })->when(isset($filters['taxable']), function ($q) use ($filters) {
            $q->where('taxable', $filters['taxable']);
        });

        $limit = $filters['limit'] ?? 10;

        return $query->select('id', 'code', 'name')->latest()->limit($limit)->get();
    }
}
