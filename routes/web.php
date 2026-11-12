<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Finance\FinanceController;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Inventory\StockOpnameController;
use App\Http\Controllers\Master\BrandController;
use App\Http\Controllers\Master\CategoryController;
use App\Http\Controllers\Master\CustomerController;
use App\Http\Controllers\Master\ProductController;
use App\Http\Controllers\Master\SupplierController;
use App\Http\Controllers\Master\UnitController;
use App\Http\Controllers\Master\WarehouseController;
use App\Http\Controllers\Pos\PosController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\System\AuditLogController;
use App\Http\Controllers\System\SettingController;
use App\Http\Controllers\System\UserController;
use App\Http\Controllers\Transaction\PurchaseController;
use App\Http\Controllers\Transaction\SaleController;
use App\Http\Controllers\Transaction\StockOutController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // Dashboard
    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Smart POS (Kasir Pintar)
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index')->middleware('permission:pos.access');
        Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout')->middleware('permission:pos.create-sale');
        Route::get('/receipt/{sale}', [PosController::class, 'receipt'])->name('receipt');
        Route::get('/history', [PosController::class, 'history'])->name('history');
        Route::post('/void/{sale}', [PosController::class, 'void'])->name('void')->middleware('permission:pos.void-sale');
    });

    // Master Data
    Route::prefix('master')->name('master.')->group(function () {
        // Products
        Route::get('products/export-template', [ProductController::class, 'exportTemplate'])->name('products.export-template');
        Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
        Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
        Route::post('products/{product}/stock', [ProductController::class, 'addStock'])->name('products.stock.add');
        Route::resource('products', ProductController::class);

        // Categories
        Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);

        // Brands (Merek Produk)
        Route::resource('brands', BrandController::class)->except(['create', 'show', 'edit']);

        // Units
        Route::resource('units', UnitController::class)->except(['create', 'show', 'edit']);

        // Warehouses & Locations
        Route::resource('warehouses', WarehouseController::class)->except(['create', 'show', 'edit']);
        Route::post('warehouses/location', [WarehouseController::class, 'storeLocation'])->name('warehouses.location.store');
        Route::delete('warehouses/location/{location}', [WarehouseController::class, 'destroyLocation'])->name('warehouses.location.destroy');
    });

    // Mitra (Suppliers & Customers)
    Route::prefix('mitra')->name('mitra.')->group(function () {
        Route::resource('suppliers', SupplierController::class);
        Route::resource('customers', CustomerController::class);
    });

    // Inventory / Gudang
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('stock-summary', [InventoryController::class, 'stockSummary'])->name('stock-summary');
        Route::get('stock-card/{productId?}', [InventoryController::class, 'stockCard'])->name('stock-card');
        Route::get('min-stock', [InventoryController::class, 'minStock'])->name('min-stock');
        Route::get('expired-stock', [InventoryController::class, 'expiredStock'])->name('expired-stock');

        // Stock Opname
        Route::resource('opname', StockOpnameController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('opname/{opname}/adjust', [StockOpnameController::class, 'adjust'])->name('opname.adjust');
    });

    // Transaksi (Penjualan, Purchases / Barang Masuk & Stock Out / Barang Keluar)
    Route::prefix('transactions')->name('transactions.')->group(function () {
        // Sales (Penjualan Toko & Pencatatan Transaksi Manual)
        Route::get('sales/{sale}/print', [SaleController::class, 'print'])->name('sales.print');
        Route::post('sales/{sale}/payment', [SaleController::class, 'updatePayment'])->name('sales.payment');
        Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);

        // Purchases (Barang Masuk)
        Route::get('purchases/{purchase}/print', [PurchaseController::class, 'print'])->name('purchases.print');
        Route::post('purchases/{purchase}/receive', [PurchaseController::class, 'receive'])->name('purchases.receive');
        Route::post('purchases/{purchase}/invoice-image', [PurchaseController::class, 'uploadInvoice'])->name('purchases.invoice-image');
        Route::resource('purchases', PurchaseController::class);

        // Stock Outs (Barang Keluar)
        Route::get('stock-outs/{stockOut}/pdf', [StockOutController::class, 'exportPdf'])->name('stock-outs.pdf');
        Route::get('stock-outs/{stockOut}/csv', [StockOutController::class, 'exportCsv'])->name('stock-outs.csv');
        Route::resource('stock-outs', StockOutController::class)->only(['index', 'create', 'store', 'show']);
    });

    // Keuangan (Finance)
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('payments', [FinanceController::class, 'payments'])->name('payments');
        Route::get('receivables', [FinanceController::class, 'receivables'])->name('receivables');
        Route::post('receivables/{receivable}/pay', [FinanceController::class, 'payReceivable'])->name('receivables.pay');
        Route::get('payables', [FinanceController::class, 'payables'])->name('payables');
        Route::post('payables/{payable}/pay', [FinanceController::class, 'payPayable'])->name('payables.pay');
        Route::get('cash', [FinanceController::class, 'cash'])->name('cash');
        Route::post('cash', [FinanceController::class, 'storeCash'])->name('cash.store');
        Route::get('expenses', [FinanceController::class, 'expenses'])->name('expenses');
        Route::post('expenses', [FinanceController::class, 'storeExpense'])->name('expenses.store');
    });

    // Laporan (Reports)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('purchases', [ReportController::class, 'purchases'])->name('purchases');
        Route::get('inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('products', [ReportController::class, 'products'])->name('products');
        Route::get('financial', [ReportController::class, 'financial'])->name('financial');
    });

    // Pengguna, Audit Log, Pengaturan Toko
    Route::resource('users', UserController::class)->except(['create', 'show', 'edit'])->middleware('permission:users.manage');
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index')->middleware('permission:audit-logs.view');
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index')->middleware('permission:settings.manage');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update')->middleware('permission:settings.manage');
});
