<?php

namespace App\Services\Webhooks;

use App\Models\Transaction;

class TransactionWebhookService
{
    public function __construct(protected Transaction $model) {}

    public function createdTransaction(array $attributes)
    {
        try {
            return $this->model->create($attributes);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
