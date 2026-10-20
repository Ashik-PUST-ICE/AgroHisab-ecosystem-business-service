<?php

namespace App\Http\Controllers\Api\V1\Business\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Payment\AllocateRemittanceRequest;
use App\Http\Requests\Business\Payment\AratdarRemittanceRequest;
use App\Http\Resources\Business\Payment\AratdarRemittanceResource;
use App\Models\Business\AratdarRemittance;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Payment\AratdarRemittanceService;
use Illuminate\Http\Request;

class AratdarPaymentController extends Controller
{
    public function __construct(
        protected AratdarRemittanceService $remittanceService
    ) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $remittances = $this->remittanceService->list(
                $request->only(['aratdar_id', 'account_id', 'payment_method', 'search', 'per_page'])
            );

            return ApiResponse::success(AratdarRemittanceResource::collection($remittances), 'Aratdar remittances retrieved successfully');
        });
    }

    public function store(AratdarRemittanceRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $remittance = $this->remittanceService->store($request->validated());

            return ApiResponse::success(new AratdarRemittanceResource($remittance), 'Aratdar remittance recorded successfully', 201);
        });
    }

    public function show(AratdarRemittance $remittance)
    {
        return $this->handleRequest(function () use ($remittance) {
            $remittance = $this->remittanceService->show($remittance);

            return ApiResponse::success(new AratdarRemittanceResource($remittance), 'Aratdar remittance retrieved successfully');
        });
    }

    public function allocate(AllocateRemittanceRequest $request, AratdarRemittance $remittance)
    {
        return $this->handleRequest(function () use ($request, $remittance) {
            $updated = $this->remittanceService->allocate($remittance, $request->validated()['allocations']);

            return ApiResponse::success(new AratdarRemittanceResource($updated), 'Remittance allocated across shipments successfully');
        });
    }
}