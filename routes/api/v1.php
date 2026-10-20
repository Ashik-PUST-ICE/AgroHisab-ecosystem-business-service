<?php

use App\Http\Controllers\Api\V1\Business\Account\AccountController;
use App\Http\Controllers\Api\V1\Business\Account\AccountLedgerController;
use App\Http\Controllers\Api\V1\Business\Aratdar\AgreementController;
use App\Http\Controllers\Api\V1\Business\Aratdar\AratdarController;
use App\Http\Controllers\Api\V1\Business\Aratdar\AratdarLedgerController;
use App\Http\Controllers\Api\V1\Business\AuditLog\AuditLogController;
use App\Http\Controllers\Api\V1\Business\Dashboard\DashboardController;
use App\Http\Controllers\Api\V1\Business\Diary\DiaryController;
use App\Http\Controllers\Api\V1\Business\Document\DocumentController;
use App\Http\Controllers\Api\V1\Business\Expense\ExpenseController;
use App\Http\Controllers\Api\V1\Business\ExpenseCategory\ExpenseCategoryController;
use App\Http\Controllers\Api\V1\Business\Market\MarketController;
use App\Http\Controllers\Api\V1\Business\Market\PriceObservationController;
use App\Http\Controllers\Api\V1\Business\Notification\NotificationController;
use App\Http\Controllers\Api\V1\Business\Payment\AratdarPaymentController;
use App\Http\Controllers\Api\V1\Business\ProductCategory\ProductCategoryController;
use App\Http\Controllers\Api\V1\Business\ProductController;
use App\Http\Controllers\Api\V1\Business\Purchase\PurchaseController;
use App\Http\Controllers\Api\V1\Business\Report\ReportController;
use App\Http\Controllers\Api\V1\Business\Shipment\ShipmentController;
use App\Http\Controllers\Api\V1\Business\Stock\StockAdjustmentController;
use App\Http\Controllers\Api\V1\Business\Stock\StockMovementController;
use App\Http\Controllers\Api\V1\Business\Supplier\SupplierController;
use App\Http\Controllers\Api\V1\Business\System\BackupController;
use App\Http\Controllers\Api\V1\Business\Transporter\TransporterController;
use App\Http\Controllers\Api\V1\Business\Unit\UnitConversionController;
use App\Http\Controllers\Api\V1\Business\Unit\UnitController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.jwt')->group(function () {

    // ── API 1: Units ───────────────────────────────────────────────────────
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

    // ── API 2: Products ────────────────────────────────────────────────────
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

    // ── API 3: Suppliers ───────────────────────────────────────────────────
    Route::prefix('suppliers')->group(function () {
        Route::get('/', [SupplierController::class, 'index']);
        Route::post('/', [SupplierController::class, 'store']);
        Route::get('{supplier}', [SupplierController::class, 'show']);
        Route::put('{supplier}', [SupplierController::class, 'update']);
        Route::delete('{supplier}', [SupplierController::class, 'destroy']);
        Route::get('{supplier}/ledger', [SupplierController::class, 'ledger']);
    });

    // ── API 4: Aratdars + Ledger(18) + Agreement(19) ──────────────────────
    Route::prefix('aratdars')->group(function () {
        Route::get('/', [AratdarController::class, 'index']);
        Route::post('/', [AratdarController::class, 'store']);
        Route::get('{aratdar}', [AratdarController::class, 'show']);
        Route::put('{aratdar}', [AratdarController::class, 'update']);
        Route::delete('{aratdar}', [AratdarController::class, 'destroy']);
        Route::get('{aratdar}/ledger', [AratdarLedgerController::class, 'ledger']);
        Route::get('{aratdar}/agreement', [AgreementController::class, 'show']);
        Route::put('{aratdar}/agreement', [AgreementController::class, 'update']);
    });

    // ── API 5: Markets + Price Observations(24) ────────────────────────────
    Route::prefix('markets')->group(function () {
        Route::get('/', [MarketController::class, 'index']);
        Route::post('/', [MarketController::class, 'store']);
        Route::get('{market}', [MarketController::class, 'show']);
        Route::put('{market}', [MarketController::class, 'update']);
        Route::delete('{market}', [MarketController::class, 'destroy']);
        // API 24: Daily market price observations
        Route::get('{market}/price-observations', [PriceObservationController::class, 'index']);
        Route::post('{market}/price-observations', [PriceObservationController::class, 'store']);
    });
    // API 24: Delete a specific price observation
    Route::delete('price-observations/{priceObservation}', [PriceObservationController::class, 'destroy']);

    // ── API 6: Accounts + Ledger(22) + Transfer(22) ───────────────────────
    Route::post('accounts/transfer', [AccountLedgerController::class, 'transfer']);
    Route::prefix('accounts')->group(function () {
        Route::get('/', [AccountController::class, 'index']);
        Route::post('/', [AccountController::class, 'store']);
        Route::get('{account}', [AccountController::class, 'show']);
        Route::put('{account}', [AccountController::class, 'update']);
        Route::delete('{account}', [AccountController::class, 'destroy']);
        Route::get('{account}/ledger', [AccountLedgerController::class, 'ledger']);
    });

    // ── API 7: Expense Categories ──────────────────────────────────────────
    Route::prefix('expense-categories')->group(function () {
        Route::get('/', [ExpenseCategoryController::class, 'index']);
        Route::post('/', [ExpenseCategoryController::class, 'store']);
        Route::get('{expenseCategory}', [ExpenseCategoryController::class, 'show']);
        Route::put('{expenseCategory}', [ExpenseCategoryController::class, 'update']);
        Route::delete('{expenseCategory}', [ExpenseCategoryController::class, 'destroy']);
    });

    // ── API 8: Transporters ────────────────────────────────────────────────
    Route::prefix('transporters')->group(function () {
        Route::get('/', [TransporterController::class, 'index']);
        Route::post('/', [TransporterController::class, 'store']);
        Route::get('{transporter}', [TransporterController::class, 'show']);
        Route::put('{transporter}', [TransporterController::class, 'update']);
        Route::delete('{transporter}', [TransporterController::class, 'destroy']);
    });

    // ── API 9: Purchases ────────────────────────────────────
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

    // ── API 10: Shipments ──────────────────────────────────────────────────
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
        Route::post('{shipment}/settle', [ShipmentController::class, 'settle']);
        Route::get('{shipment}/settlement', [ShipmentController::class, 'settlement']);
    });

    // ── API 11: Payments ───────────────────────────────────────────────────
    Route::prefix('payments')->group(function () {
        Route::get('aratdar-remittance', [AratdarPaymentController::class, 'index']);
        Route::post('aratdar-remittance', [AratdarPaymentController::class, 'store']);
        Route::get('aratdar-remittance/{remittance}', [AratdarPaymentController::class, 'show']);
        Route::post('{remittance}/allocate', [AratdarPaymentController::class, 'allocate']);
    });

    // ── API 12: Dashboard ──────────────────────────────────────────────────
    Route::prefix('dashboard')->group(function () {
        Route::get('metrics', [DashboardController::class, 'metrics']);
    });

    // ── API 13 + 23: Reports ──────────────────────────────────────────────
    Route::prefix('reports')->group(function () {
        Route::get('profitability', [ReportController::class, 'profitability']);
        Route::get('cashbook', [ReportController::class, 'cashbook']);
        Route::get('inventory-valuation', [ReportController::class, 'inventoryValuation']);
        Route::get('receivables', [ReportController::class, 'receivables']);
        Route::get('payables', [ReportController::class, 'payables']);
        Route::get('sales-by-product', [ReportController::class, 'salesByProduct']);
    });

    // ── API 14: Documents ─────────────────────────────────────────────────
    Route::prefix('documents')->group(function () {
        Route::get('/', [DocumentController::class, 'index']);
        Route::post('/', [DocumentController::class, 'store']);
        Route::get('{document}', [DocumentController::class, 'show']);
        Route::delete('{document}', [DocumentController::class, 'destroy']);
    });

    // ── API 15: Expenses ──────────────────────────────────────────────────
    Route::prefix('expenses')->group(function () {
        Route::get('/', [ExpenseController::class, 'index']);
        Route::post('/', [ExpenseController::class, 'store']);
        Route::get('{expense}', [ExpenseController::class, 'show']);
        Route::put('{expense}', [ExpenseController::class, 'update']);
        Route::delete('{expense}', [ExpenseController::class, 'destroy']);
    });

    // ── API 16: Audit Logs ────────────────────────────────────────────────
    Route::prefix('audit-logs')->group(function () {
        Route::get('/', [AuditLogController::class, 'index']);
    });

    // ── API 17: System Backups ────────────────────────────────────────────
    Route::prefix('system/backups')->group(function () {
        Route::get('/', [BackupController::class, 'index']);
        Route::post('/', [BackupController::class, 'store']);
    });

    // ── API 20: Notifications ─────────────────────────────────────────────
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('read-all', [NotificationController::class, 'markAllRead']);
        Route::post('{notification}/read', [NotificationController::class, 'markRead']);
    });


    // ── API 25: Stock Loss & Adjustments (পচন, শুকিয়ে ওজন হ্রাস, ঘাটতি, ড্যামেজ) ──
    Route::prefix('stock-adjustments')->group(function () {
        Route::get('/', [StockAdjustmentController::class, 'index']);
        Route::post('/', [StockAdjustmentController::class, 'store']);
        Route::get('{stockAdjustment}', [StockAdjustmentController::class, 'show']);
        Route::delete('{stockAdjustment}', [StockAdjustmentController::class, 'destroy']);
    });

    // ── API 21: Stock Movements ───────────────────────────────────────────
    Route::prefix('stock-movements')->group(function () {
        Route::get('/', [StockMovementController::class, 'index']);
        Route::get('balance', [StockMovementController::class, 'balance']);
    });

    // ── API 26: Business Diary / Timeline ─────────────────────────────────
    Route::prefix('diary')->group(function () {
        Route::get('/', [DiaryController::class, 'index']);
        Route::post('/', [DiaryController::class, 'store']);
        Route::put('{diary}', [DiaryController::class, 'update']);
        Route::delete('{diary}', [DiaryController::class, 'destroy']);
    });
});