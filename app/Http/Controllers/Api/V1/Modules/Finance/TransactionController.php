<?php

namespace App\Http\Controllers\Api\V1\Modules\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Finance\TransactionRequest;
use App\Http\Resources\Modules\Finance\TransactionResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Finance\TransactionService;

class TransactionController extends Controller
{
    public function __construct(protected TransactionService $TransactionService) {}

    public function index(TransactionRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->TransactionService->index($request->validated());

            return ApiResponse::success(TransactionResource::collection($data), 'Data fetched successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->TransactionService->show($id);

            return ApiResponse::success(TransactionResource::make($data));
        });
    }
}
