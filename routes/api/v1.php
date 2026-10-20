<?php

use App\Http\Controllers\Api\V1\Business\Account\AccountController;
use App\Http\Controllers\Api\V1\Business\Aratdar\AratdarController;
use App\Http\Controllers\Api\V1\Business\ExpenseCategory\ExpenseCategoryController;
use App\Http\Controllers\Api\V1\Business\Market\MarketController;
use App\Http\Controllers\Api\V1\Business\Payment\AratdarPaymentController;
use App\Http\Controllers\Api\V1\Business\ProductCategory\ProductCategoryController;
use App\Http\Controllers\Api\V1\Business\ProductController;
use App\Http\Controllers\Api\V1\Business\Purchase\PurchaseController;
use App\Http\Controllers\Api\V1\Business\Shipment\ShipmentController;
use App\Http\Controllers\Api\V1\Business\Supplier\SupplierController;
use App\Http\Controllers\Api\V1\Business\Transporter\TransporterController;
use App\Http\Controllers\Api\V1\Business\Unit\UnitConversionController;
use App\Http\Controllers\Api\V1\Business\Unit\UnitController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.jwt')->group(function () {

    // API 1: Units & Unit Conversions
    Route::prefix('units')->group(function () {
        Route::get('/', [UnitController::class, 'index']);
        Route::post('/', [UnitController::class, 'store']);
        Route::get('{unit}', [UnitController::class, 'show']);
        Route::put('{unit}', [UnitController::class, 'update']);
        Route::delete('{unit}', [UnitController::class, 'destroy']);
    });

    Route::prefix('unit-conversions')->group(function () {
        Route::get('/', [UnitConversionController::class, 'index']);
        Route::post('/', [UnitConversionController::class, 'store']);
        Route::post('convert', [UnitConversionController::class, 'convert']);
        Route::get('{conversion}', [UnitConversionController::class, 'show']);
        Route::put('{conversion}', [UnitConversionController::class, 'update']);
        Route::delete('{conversion}', [UnitConversionController::class, 'destroy']);
    });

    // API 2: Product Categories & Products
    Route::prefix('product-categories')->group(function () {
        Route::get('/', [ProductCategoryController::class, 'index']);
        Route::post('/', [ProductCategoryController::class, 'store']);
        Route::get('{productCategory}', [ProductCategoryController::class, 'show']);
        Route::put('{productCategory}', [ProductCategoryController::class, 'update']);
        Route::delete('{productCategory}', [ProductCategoryController::class, 'destroy']);
    });

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/', [ProductController::class, 'store']);
        Route::get('{product}', [ProductController::class, 'show']);
        Route::put('{product}', [ProductController::class, 'update']);
        Route::delete('{product}', [ProductController::class, 'destroy']);
    });

    // API 3: Suppliers + Ledger
    Route::prefix('suppliers')->group(function () {
        Route::get('/', [SupplierController::class, 'index']);
        Route::post('/', [SupplierController::class, 'store']);
        Route::get('{supplier}', [SupplierController::class, 'show']);
        Route::put('{supplier}', [SupplierController::class, 'update']);
        Route::delete('{supplier}', [SupplierController::class, 'destroy']);
        Route::get('{supplier}/ledger', [SupplierController::class, 'ledger']);
    });

    // API 4: Aratdars
    Route::prefix('aratdars')->group(function () {
        Route::get('/', [AratdarController::class, 'index']);
        Route::post('/', [AratdarController::class, 'store']);
        Route::get('{aratdar}', [AratdarController::class, 'show']);
        Route::put('{aratdar}', [AratdarController::class, 'update']);
        Route::delete('{aratdar}', [AratdarController::class, 'destroy']);
    });

    // API 5: Markets
    Route::prefix('markets')->group(function () {
        Route::get('/', [MarketController::class, 'index']);
        Route::post('/', [MarketController::class, 'store']);
        Route::get('{market}', [MarketController::class, 'show']);
        Route::put('{market}', [MarketController::class, 'update']);
        Route::delete('{market}', [MarketController::class, 'destroy']);
    });

    // API 6: Financial Accounts
    Route::prefix('accounts')->group(function () {
        Route::get('/', [AccountController::class, 'index']);
        Route::post('/', [AccountController::class, 'store']);
        Route::get('{account}', [AccountController::class, 'show']);
        Route::put('{account}', [AccountController::class, 'update']);
        Route::delete('{account}', [AccountController::class, 'destroy']);
    });

    // API 7: Expense Categories
    Route::prefix('expense-categories')->group(function () {
        Route::get('/', [ExpenseCategoryController::class, 'index']);
        Route::post('/', [ExpenseCategoryController::class, 'store']);
        Route::get('{expenseCategory}', [ExpenseCategoryController::class, 'show']);
        Route::put('{expenseCategory}', [ExpenseCategoryController::class, 'update']);
        Route::delete('{expenseCategory}', [ExpenseCategoryController::class, 'destroy']);
    });

    // API 8: Transporters
    Route::prefix('transporters')->group(function () {
        Route::get('/', [TransporterController::class, 'index']);
        Route::post('/', [TransporterController::class, 'store']);
        Route::get('{transporter}', [TransporterController::class, 'show']);
        Route::put('{transporter}', [TransporterController::class, 'update']);
        Route::delete('{transporter}', [TransporterController::class, 'destroy']);
    });

    // API 9: Purchases + Stock Receipt + Supplier Payments
    Route::prefix('purchases')->group(function () {
        Route::get('/', [PurchaseController::class, 'index']);
        Route::post('/', [PurchaseController::class, 'store']);
        Route::get('{purchase}', [PurchaseController::class, 'show']);
        Route::put('{purchase}', [PurchaseController::class, 'update']);
        Route::delete('{purchase}', [PurchaseController::class, 'destroy']);
        Route::post('{purchase}/receive', [PurchaseController::class, 'receive']);
        Route::get('{purchase}/payments', [PurchaseController::class, 'payments']);
        Route::post('{purchase}/payments', [PurchaseController::class, 'storePayment']);
    });

    // API 10: Shipments + Dispatch + Goods Receipt + Challan + Sales + Stock Balance + Settlement Preview
    Route::prefix('shipments')->group(function () {
        Route::get('/', [ShipmentController::class, 'index']);
        Route::post('/', [ShipmentController::class, 'store']);
        Route::get('{shipment}', [ShipmentController::class, 'show']);
        Route::put('{shipment}', [ShipmentController::class, 'update']);
        Route::delete('{shipment}', [ShipmentController::class, 'destroy']);
        Route::post('{shipment}/dispatch', [ShipmentController::class, 'dispatch']);
        Route::post('{shipment}/receive', [ShipmentController::class, 'receive']);
        Route::get('{shipment}/challan', [ShipmentController::class, 'challan']);
        Route::get('{shipment}/challan-pdf', [ShipmentController::class, 'challan']);
        Route::get('{shipment}/sales', [ShipmentController::class, 'sales']);
        Route::post('{shipment}/sales', [ShipmentController::class, 'storeSale']);
        Route::get('{shipment}/stock', [ShipmentController::class, 'stock']);
        Route::get('{shipment}/settlement-preview', [ShipmentController::class, 'settlementPreview']);
    });

    // API 11: Aratdar Remittances & Allocations
    Route::prefix('payments')->group(function () {
        Route::get('aratdar-remittance', [AratdarPaymentController::class, 'index']);
        Route::post('aratdar-remittance', [AratdarPaymentController::class, 'store']);
        Route::get('aratdar-remittance/{remittance}', [AratdarPaymentController::class, 'show']);
        Route::post('{remittance}/allocate', [AratdarPaymentController::class, 'allocate']);
    });
});