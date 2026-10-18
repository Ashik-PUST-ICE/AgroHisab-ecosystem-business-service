<?php

namespace App\Http\Controllers\Api\V1\Business;

use App\Http\Controllers\Controller;
use App\Http\Resources\Business\Outlet\OutletResource;
use App\Http\Requests\Business\Outlet\OutletRequest;
use App\Models\Business\Outlet;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Business\Outlet\OutletService;
use Illuminate\Http\Request;

class OutletController extends Controller
{
    public function __construct(protected OutletService $outletService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $outlets = $this->outletService->index($request->only(['search', 'per_page']));

            return ApiResponse::success($outlets, 'Outlets retrieved successfully');
        });
    }

    public function store(OutletRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $outlet = $this->outletService->store($request->validated());

            return ApiResponse::success(new OutletResource($outlet), 'Outlet created successfully', 201);
        });
    }

    public function show(Outlet $outlet)
    {
        return $this->handleRequest(function () use ($outlet) {
            $outlet = $this->outletService->show($outlet);

            return ApiResponse::success(new OutletResource($outlet), 'Outlet retrieved successfully');
        });
    }

    public function update(OutletRequest $request, Outlet $outlet)
    {
        return $this->handleRequest(function () use ($request, $outlet) {
            $outlet = $this->outletService->update($outlet, $request->validated());

            return ApiResponse::success(new OutletResource($outlet), 'Outlet updated successfully');
        });
    }

    public function destroy(Outlet $outlet)
    {
        return $this->handleRequest(function () use ($outlet) {
            $this->outletService->destroy($outlet);

            return ApiResponse::success(null, 'Outlet deleted successfully');
        });
    }

    public function regenerateQr(Outlet $outlet)
    {
        return $this->handleRequest(function () use ($outlet) {
            $outlet = $this->outletService->regenerateQr($outlet);

            return ApiResponse::success(new OutletResource($outlet), 'QR regenerated successfully');
        });
    }

    public function downloadQr(Outlet $outlet)
    {
        return $this->handleRequest(function () use ($outlet) {
            $qr = $this->outletService->downloadQr($outlet);
            $qrApi = "https://api.qrserver.com/v1/create-qr-code/?size=400x400&data={$qr['url']}";

            return redirect($qrApi);
        });
    }

    public function deactivateQr(Outlet $outlet)
    {
        return $this->handleRequest(function () use ($outlet) {
            $outlet = $this->outletService->deactivateQr($outlet);

            return ApiResponse::success(new OutletResource($outlet), 'QR deactivated successfully');
        });
    }
}
