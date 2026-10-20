<?php

namespace App\Http\Controllers\Api\V1\Business\Aratdar;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Aratdar\AgreementRequest;
use App\Models\Business\Aratdar;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Aratdar\AgreementService;

class AgreementController extends Controller
{
    public function __construct(protected AgreementService $agreementService) {}

    /**
     * GET /api/v1/aratdars/{aratdar}/agreement
     */
    public function show(Aratdar $aratdar)
    {
        return $this->handleRequest(function () use ($aratdar) {
            return ApiResponse::success(
                $this->agreementService->show($aratdar),
                'Commission agreement retrieved successfully'
            );
        });
    }

    /**
     * PUT /api/v1/aratdars/{aratdar}/agreement
     */
    public function update(AgreementRequest $request, Aratdar $aratdar)
    {
        return $this->handleRequest(function () use ($request, $aratdar) {
            return ApiResponse::success(
                $this->agreementService->update($aratdar, $request->validated()),
                'Commission agreement updated successfully'
            );
        });
    }
}