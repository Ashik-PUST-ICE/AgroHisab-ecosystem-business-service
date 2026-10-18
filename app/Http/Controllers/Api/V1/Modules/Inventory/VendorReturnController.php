<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Inventory\VendorReturnRequest;
use App\Http\Resources\Modules\Inventory\VendorReturnResource;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\VendorReturnService;
use Illuminate\Http\Request;

class VendorReturnController extends Controller
{
    public function __construct(protected VendorReturnService $vendorReturnService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->vendorReturnService->index($request->all());

            return ApiResponse::success(VendorReturnResource::collection($data->load(['stockCategory', 'stockProduct', 'brand', 'unit', 'vendor', 'account', 'replaceProduct', 'transactions'])), 'Data fetched successfully');
        });
    }

    public function store(VendorReturnRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->vendorReturnService->store($request->validated());

            return ApiResponse::success(VendorReturnResource::make($data->load(['stockCategory', 'stockProduct', 'brand', 'unit', 'vendor', 'account', 'replaceProduct', 'transactions'])), 'Vendor return created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $data = $this->vendorReturnService->show($id, ['stockCategory', 'stockProduct', 'brand', 'unit', 'vendor', 'account', 'replaceProduct', 'transactions']);

            return ApiResponse::success(VendorReturnResource::make($data));
        });
    }
}
