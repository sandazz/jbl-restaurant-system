<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\WastageController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\QrMenuController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\TierDiscountController;
use App\Http\Controllers\ClerkBalancingController;
use App\Http\Controllers\RawMaterialController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Public QR Menu Routes (no auth required)
Route::get('/menu/scan', [QrMenuController::class, 'viewMenu'])->name('menu.view');
Route::get('/api/menu/category/{categoryId}', [QrMenuController::class, 'getProductsByCategory']);
Route::get('/qr-code/generate', [QrMenuController::class, 'generateQr'])->name('qr.generate');
Route::get('/qr-code/download', [QrMenuController::class, 'downloadQr'])->name('qr.download');
Route::get('/qr-code/pdf', [QrMenuController::class, 'downloadQrPdf'])->name('qr.pdf');

// Auth Routes
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // QR Menu Management (Admin)
    Route::get('/qr-menu/admin', function () {
        return view('qr-menu.qr-admin');
    })->name('qr.admin');

    // Customer CRUD routes
    Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
    Route::resource('customers', CustomerController::class);

    // Category CRUD routes
    Route::resource('categories', CategoryController::class)->except(['show']);

    // Product (Inventory) CRUD routes
    Route::get('/products/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');
    Route::resource('products', ProductController::class);
    Route::get('/inventory', function () {
        $user = Auth::user();
        $modules = $user->role->modules()->get();
        return app(ProductController::class)->index();
    })->name('inventory.index');

    // Employee CRUD routes
    Route::resource('employees', EmployeeController::class);

    // User password update
    Route::post('/users/{user}/update-password', [UserController::class, 'updatePassword']);

    // Wastage CRUD routes
    Route::resource('wastages', WastageController::class);
    Route::get('/wastage', function () {
        return app(WastageController::class)->index();
    })->name('wastage.index');

    // Placeholder routes for other modules
    Route::resource('suppliers', SupplierController::class)->except(['show']);

    // POS routes
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/order', [PosController::class, 'createOrder'])->name('pos.order.create');
    Route::get('/pos/order/{order}', [PosController::class, 'getOrder'])->name('pos.order.show');
    Route::post('/pos/order/{order}/item', [PosController::class, 'addItem'])->name('pos.item.add');
    Route::delete('/pos/order/{order}/item/{item}', [PosController::class, 'removeItem'])->name('pos.item.remove');
    Route::put('/pos/order/{order}/item/{item}', [PosController::class, 'updateItem'])->name('pos.item.update');
    Route::post('/pos/order/{order}/hold', [PosController::class, 'holdOrder'])->name('pos.order.hold');
    Route::post('/pos/order/{order}/complete', [PosController::class, 'completeOrder'])->name('pos.order.complete');
    Route::post('/pos/order/{order}/kot', [PosController::class, 'printKot'])->name('pos.order.kot');
    Route::post('/pos/order/{order}/bot', [PosController::class, 'printBot'])->name('pos.order.bot');
    Route::post('/pos/order/{order}/customer', [PosController::class, 'updateCustomer'])->name('pos.order.customer');
    Route::post('/pos/order/{order}/waiter-bill', [PosController::class, 'printWaiterBill'])->name('pos.order.waiter_bill');
    Route::post('/pos/order/{order}/live-bill', [PosController::class, 'toggleLiveBill'])->name('pos.order.live_bill');
    Route::post('/pos/order/{order}/close-table', [PosController::class, 'closeTable'])->name('pos.order.close_table');
    Route::get('/pos/table/{table}/orders', [PosController::class, 'getTableOrders'])->name('pos.table.orders');
    Route::get('/pos/tables', [PosController::class, 'getTables'])->name('pos.tables');
    Route::get('/pos/products', [PosController::class, 'getProducts'])->name('pos.products');
    Route::get('/pos/held-orders', [PosController::class, 'getHeldOrders'])->name('pos.held');
    Route::post('/pos/order/{order}/pay', [PosController::class, 'payOrder'])->name('pos.order.pay');

    // Stock adjustments
    Route::get('/inventory/adjustments', [StockAdjustmentController::class, 'index'])->name('stock.adjustments.index');
    Route::get('/inventory/adjustments/create', [StockAdjustmentController::class, 'create'])->name('stock.adjustments.create');
    Route::post('/inventory/adjustments', [StockAdjustmentController::class, 'store'])->name('stock.adjustments.store');

    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/sales-pdf', [ReportsController::class, 'exportSalesPdf'])->name('reports.export.sales');
    Route::get('/reports/export/products-pdf', [ReportsController::class, 'exportProductsPdf'])->name('reports.export.products');
    Route::get('/reports/export/combined-pdf', [ReportsController::class, 'exportCombinedPdf'])->name('reports.export.combined');

    Route::get('/settings', function () {
        $modules = auth()->user()->role->modules()->get();
        return view('modules.settings', ['modules' => $modules]);
    })->name('settings.index');

    // Tier Discount Configuration
    Route::get('/tier-discounts', [TierDiscountController::class, 'index'])->name('tier-discounts.index');
    Route::post('/tier-discounts', [TierDiscountController::class, 'store'])->name('tier-discounts.store');
    Route::post('/tier-discounts/save-all', [TierDiscountController::class, 'saveAll'])->name('tier-discounts.save-all');
    Route::delete('/tier-discounts/{tierDiscount}', [TierDiscountController::class, 'destroy'])->name('tier-discounts.destroy');

    // Raw Materials (ERP Inventory) — distinct from the product/menu-item inventory
    Route::resource('raw-materials', RawMaterialController::class);

    // Audit Logs & EOD Report
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
    Route::get('/audit-logs/eod', [AuditLogController::class, 'eodReport'])->name('audit.eod');

    // Cashier Shift Balancing
    Route::resource('clerk-balancings', ClerkBalancingController::class)
        ->except(['edit', 'update', 'destroy']);
    Route::post('/clerk-balancings/{clerkBalancing}/close', [ClerkBalancingController::class, 'closeShift'])
        ->name('clerk-balancings.close');
});
