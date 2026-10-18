<?php

namespace App\Http\Controllers\Api\V1\Modules\Inventory;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Applications\Api\ApiResponse;
use App\Services\Modules\Inventory\PurchaseService;
use App\Http\Requests\Modules\Inventory\PurchaseRequest;
use App\Http\Resources\Modules\Inventory\Purchases\PurchaseResource;
use App\Http\Resources\Modules\Inventory\Purchases\PurchaseListResource;
use App\Http\Resources\Modules\Inventory\Purchases\StockHistoryResource;

class PurchaseController extends Controller
{
    public function __construct(protected PurchaseService $PurchaseService) {}

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->PurchaseService->index($request->all());

            return ApiResponse::success(PurchaseListResource::collection($data), 'Data fetched successfully');
        });
    }

    public function store(PurchaseRequest $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $this->PurchaseService->store($request->validated());

            return ApiResponse::success($data, 'Purchase Created successfully');
        });
    }

    public function show(string $id)
    {
        return $this->handleRequest(function () use ($id) {
            $relations = [
                'requisition:id,requisition_code',
                'product:id,name,image',
                'vendor:id,first_name,last_name',
                'brand:id,name',
                'unit:id,name',
                'account:id,title,account_no',
                'purchaseItems:id,purchase_id,serial_no,mac_address,brand_id',
                'purchaseItems.brand:id,name',
            ];
            $data = $this->PurchaseService->show($id, $relations);

            return ApiResponse::success(PurchaseResource::make($data), 'Data fetched successfully');
        });
    }


        

}
