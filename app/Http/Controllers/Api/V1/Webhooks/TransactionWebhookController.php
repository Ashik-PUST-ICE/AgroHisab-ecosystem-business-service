<?php

namespace App\Http\Controllers\Api\V1\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Webhooks\TransactionWebhookService;
use Illuminate\Http\Request;

class TransactionWebhookController extends Controller
{
    public function __construct(protected TransactionWebhookService $transactionWebhookService) {}

    public function createdTransaction(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->transactionWebhookService->createdTransaction($request->all());

            return ApiResponse::success($data, 'Transaction created successfully');
        });
    }
}
