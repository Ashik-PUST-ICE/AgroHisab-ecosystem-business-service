<?php

namespace App\Http\Controllers\Api\V1\Business\Aratdar;

use App\Http\Controllers\Controller;
use App\Models\Business\Aratdar;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Aratdar\AratdarLedgerService;
use Illuminate\Http\Request;

class AratdarLedgerController extends Controller
{
    public function __construct(protected AratdarLedgerService $ledgerService) {}

    /**
     * GET /api/v1/aratdars/{aratdar}/ledger
     * Returns all sales, commissions, remittances and outstanding balance for an aratdar.
     * Optional query params: from_date, to_date
     */
    public function ledger(Request $request, Aratdar $aratdar)
    {
        return $this->handleRequest(function () use ($request, $aratdar) {
            $data = $this->ledgerService->ledger(
                $aratdar,
                $request->only(['from_date', 'to_date'])
            );
            return ApiResponse::success($data, 'Aratdar ledger retrieved successfully');
        });
    }
}