<?php

use App\Http\Controllers\Api\V1\Business\Aratdar\AratdarController;
use App\Http\Controllers\Api\V1\Business\ProductCategory\ProductCategoryController;
use App\Http\Controllers\Api\V1\Business\ProductController;
use App\Http\Controllers\Api\V1\Business\Supplier\SupplierController;
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

    // API 3: Suppliers
    Route::prefix('suppliers')->group(function () {
        Route::get('/', [SupplierController::class, 'index']);
        Route::post('/', [SupplierController::class, 'store']);
        Route::get('{supplier}', [SupplierController::class, 'show']);
        Route::put('{supplier}', [SupplierController::class, 'update']);
        Route::delete('{supplier}', [SupplierController::class, 'destroy']);
    });

    // API 4: Aratdars (Commission Agents)
    Route::prefix('aratdars')->group(function () {
        Route::get('/', [AratdarController::class, 'index']);
        Route::post('/', [AratdarController::class, 'store']);
        Route::get('{aratdar}', [AratdarController::class, 'show']);
        Route::put('{aratdar}', [AratdarController::class, 'update']);
        Route::delete('{aratdar}', [AratdarController::class, 'destroy']);
    });
});
