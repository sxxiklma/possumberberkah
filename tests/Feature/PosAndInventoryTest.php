<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Purchase;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Services\FifoInventoryService;
use App\Services\PosSaleService;
use App\Services\PurchaseService;
use App\Services\UnitConversionService;
use Database\Seeders\RoleAndPermissionSeeder;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PosAndInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $cashier;
    protected Warehouse $warehouse;
    protected WarehouseLocation $location;
    protected Unit $pcsUnit;
    protected Unit $packUnit;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        // Create Super Admin User
        $this->superAdmin = User::create([
            'name' => 'Super Administrator',
            'username' => 'superadmin_test',
            'email' => 'superadmin_test@sumberberkah.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->superAdmin->assignRole('Super Admin');

        // Create Cashier User
        $this->cashier = User::create([
            'name' => 'Kasir Toko',
            'username' => 'kasir_test',
            'email' => 'kasir_test@sumberberkah.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->cashier->assignRole('Kasir');

        // Master setup
        $this->pcsUnit = Unit::create(['code' => 'PCS', 'name' => 'Pieces', 'symbol' => 'PCS']);
        $this->packUnit = Unit::create(['code' => 'PACK', 'name' => 'Pack', 'symbol' => 'PACK']);
        $this->category = Category::create(['code' => 'CAT-01', 'name' => 'Bahan Bangunan', 'slug' => 'bahan-bangunan']);
        $this->warehouse = Warehouse::create(['code' => 'GUD-01', 'name' => 'Gudang Utama', 'type' => 'retail']);
        $this->location = WarehouseLocation::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'LOC-01',
            'rack_code' => 'RAK-01',
            'area' => 'Area Depan',
        ]);

        Setting::set('fifo_strict_mode', '1');
        Setting::set('prevent_negative_stock', '1');
    }

    /**
     * TEST 1: Sistem Multi-Satuan & Konversi Stok (10 PACK = 1.000 PCS, jual 25 PCS -> 9 PACK + 75 PCS)
     */
    public function test_multi_unit_conversion_and_composite_display(): void
    {
        // Setup Product Paku: Base PCS, 1 PACK = 100 PCS
        $paku = Product::create([
            'sku' => 'PKU-01',
            'name' => 'Paku Kayu 5cm',
            'slug' => 'paku-kayu-5cm',
            'category_id' => $this->category->id,
            'base_unit_id' => $this->pcsUnit->id,
            'buy_price' => 200,
            'selling_price' => 350,
            'min_stock' => 50,
            'warehouse_location_id' => $this->location->id,
            'is_active' => true,
        ]);

        ProductUnit::create([
            'product_id' => $paku->id,
            'unit_id' => $this->packUnit->id,
            'conversion_factor' => 100, // 1 PACK = 100 PCS
            'selling_price' => 32000,
            'is_base' => false,
        ]);

        // Input 10 PACK (1.000 PCS) via initial batch
        InventoryBatch::create([
            'batch_number' => 'BATCH-TEST-001',
            'product_id' => $paku->id,
            'warehouse_id' => $this->warehouse->id,
            'warehouse_location_id' => $this->location->id,
            'initial_quantity' => 1000,
            'available_quantity' => 1000,
            'cost_price' => 200,
            'received_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        // Check stock before sale: 1000 PCS
        $this->assertEquals(1000, $paku->fresh()->current_stock);
        $compositeInitial = UnitConversionService::formatCompositeUnits($paku, 1000);
        $this->assertEquals('10 PACK', $compositeInitial);

        // Customer buys 25 PCS -> reduce 25 PCS
        $batch = InventoryBatch::where('product_id', $paku->id)->first();
        $batch->available_quantity -= 25;
        $batch->save();

        // Stock remaining should be 975 PCS
        $remainingStock = $paku->fresh()->current_stock;
        $this->assertEquals(975, $remainingStock);

        // Multi-unit display must be "9 PACK + 75 PCS"
        $compositeRemaining = UnitConversionService::formatCompositeUnits($paku, $remainingStock);
        $this->assertEquals('9 PACK + 75 PCS', $compositeRemaining);
    }

    /**
     * TEST 2: FIFO Engine: Batch A (100 @ Rp 1.000), Batch B (100 @ Rp 1.200). Jual 150 -> HPP Rp 160.000
     */
    public function test_fifo_inventory_batch_deduction_and_cogs_calculation(): void
    {
        $fifoService = app(FifoInventoryService::class);

        $product = Product::create([
            'sku' => 'TEST-FIFO-01',
            'name' => 'Beras Pandan Wangi 1kg',
            'slug' => 'beras-pandan-wangi-1kg',
            'category_id' => $this->category->id,
            'base_unit_id' => $this->pcsUnit->id,
            'buy_price' => 1000,
            'selling_price' => 1500,
            'min_stock' => 10,
            'warehouse_location_id' => $this->location->id,
            'is_active' => true,
        ]);

        // Batch A: 100 unit @ Rp 1.000 (masuk kemarin)
        $batchA = InventoryBatch::create([
            'batch_number' => 'BATCH-A',
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'initial_quantity' => 100,
            'available_quantity' => 100,
            'cost_price' => 1000,
            'received_date' => now()->subDay()->toDateString(),
            'status' => 'active',
        ]);

        // Batch B: 100 unit @ Rp 1.200 (masuk hari ini)
        $batchB = InventoryBatch::create([
            'batch_number' => 'BATCH-B',
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'initial_quantity' => 100,
            'available_quantity' => 100,
            'cost_price' => 1200,
            'received_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        $this->assertEquals(200, $product->fresh()->current_stock);

        // Penjualan 150 unit via FIFO
        $deduction = $fifoService->deductStockFifo(
            product: $product,
            requiredBaseQuantity: 150,
            referenceNumber: 'INV-TEST-001',
            movementType: 'sale',
            userId: $this->cashier->id
        );

        // Calculation: (100 * 1.000) + (50 * 1.200) = 100.000 + 60.000 = Rp 160.000
        $this->assertEquals(160000, $deduction['total_cogs']);
        $this->assertCount(2, $deduction['batches_used']);

        // Check Batch A: habis (0) and status depleted
        $batchA->refresh();
        $this->assertEquals(0, $batchA->available_quantity);
        $this->assertEquals('depleted', $batchA->status);

        // Check Batch B: sisa 50 and status active
        $batchB->refresh();
        $this->assertEquals(50, $batchB->available_quantity);
        $this->assertEquals('active', $batchB->status);

        // Total product stock remaining: 50 unit
        $this->assertEquals(50, $product->fresh()->current_stock);
    }

    /**
     * TEST 3: Pembayaran DP & Tempo Piutang (Total Rp 1.000.000, DP Rp 400.000 -> Sisa Piutang Rp 600.000)
     */
    public function test_pos_checkout_down_payment_and_receivable_creation(): void
    {
        $posService = app(PosSaleService::class);

        $customer = Customer::create([
            'code' => 'CUST-TEST-01',
            'name' => 'PT Bangun Jaya Mandiri',
            'category' => 'grosir',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'SEMEN-TEST',
            'name' => 'Semen Padang 50kg',
            'slug' => 'semen-padang-50kg',
            'category_id' => $this->category->id,
            'base_unit_id' => $this->pcsUnit->id,
            'buy_price' => 50000,
            'selling_price' => 100000, // Rp 100.000 per sak
            'min_stock' => 5,
            'warehouse_location_id' => $this->location->id,
            'is_active' => true,
        ]);

        InventoryBatch::create([
            'batch_number' => 'BATCH-SMN-01',
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'initial_quantity' => 20,
            'available_quantity' => 20,
            'cost_price' => 50000,
            'received_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        // Customer buys 10 Sak @ Rp 100.000 = Rp 1.000.000
        // Bayar DP Rp 400.000 via Tempo
        $cartData = [
            'customer_id' => $customer->id,
            'payment_method' => 'tempo',
            'paid_amount' => 400000,
            'discount_amount' => 0,
            'due_date' => now()->addDays(30)->toDateString(),
            'items' => [
                [
                    'product_id' => $product->id,
                    'unit_id' => $this->pcsUnit->id,
                    'quantity' => 10,
                    'unit_price' => 100000,
                    'discount_amount' => 0,
                ],
            ],
        ];

        $sale = $posService->createSale($cartData, $this->cashier);

        // Verify Sale record
        $this->assertEquals(1000000, $sale->total_amount);
        $this->assertEquals(400000, $sale->paid_amount);
        $this->assertEquals('dp', $sale->payment_status);

        // Verify Receivable created
        $receivable = Receivable::where('sale_id', $sale->id)->first();
        $this->assertNotNull($receivable);
        $this->assertEquals(1000000, $receivable->total_amount);
        $this->assertEquals(400000, $receivable->paid_amount);
        $this->assertEquals(600000, $receivable->remaining_balance);
        $this->assertEquals('partial', $receivable->status);

        // Verify Initial Payment logged
        $payment = Payment::where('reference_id', $sale->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(400000, $payment->amount);
    }

    /**
     * TEST 4: Penerimaan Barang Masuk (PO 10 PACK x 100 PCS -> Stok +1.000 PCS pada Gudang)
     */
    public function test_purchase_receiving_creates_fifo_batch_and_updates_stock(): void
    {
        $purchaseService = app(PurchaseService::class);

        $supplier = Supplier::create([
            'code' => 'SUPP-TEST-01',
            'name' => 'Distributor Paku Nasional',
            'is_active' => true,
        ]);

        $paku = Product::create([
            'sku' => 'PKU-BUY-01',
            'name' => 'Paku Beton 7cm',
            'slug' => 'paku-beton-7cm',
            'category_id' => $this->category->id,
            'base_unit_id' => $this->pcsUnit->id,
            'buy_price' => 300,
            'selling_price' => 500,
            'min_stock' => 100,
            'warehouse_location_id' => $this->location->id,
            'is_active' => true,
        ]);

        ProductUnit::create([
            'product_id' => $paku->id,
            'unit_id' => $this->packUnit->id,
            'conversion_factor' => 100, // 1 PACK = 100 PCS
            'is_base' => false,
        ]);

        $this->assertEquals(0, $paku->fresh()->current_stock);

        // Create PO
        $poData = [
            'supplier_id' => $supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'purchase_date' => now()->toDateString(),
            'notes' => 'Pesanan stok bulanan',
            'items' => [
                [
                    'product_id' => $paku->id,
                    'unit_id' => $this->packUnit->id,
                    'quantity' => 10, // 10 PACK
                    'unit_price' => 30000, // Rp 30.000 per PACK (Rp 300 / PCS)
                    'discount_amount' => 0,
                    'tax' => 0,
                ],
            ],
        ];

        $purchase = $purchaseService->createPurchase($poData, $this->superAdmin);
        $this->assertEquals('draft', $purchase->status);

        // Konfirmasi Penerimaan Barang di Gudang
        $receivedPurchase = $purchaseService->receivePurchase($purchase, $this->superAdmin);
        $this->assertEquals('received', $receivedPurchase->status);

        // Check stock: 10 PACK * 100 = 1,000 PCS
        $paku->refresh();
        $this->assertEquals(1000, $paku->current_stock);

        // Check FIFO Batch created
        $batch = InventoryBatch::where('product_id', $paku->id)->first();
        $this->assertNotNull($batch);
        $this->assertEquals(1000, $batch->initial_quantity);
        $this->assertEquals(1000, $batch->available_quantity);
        $this->assertEquals(300, $batch->cost_price);
    }

    /**
     * TEST 5: Void / Pembatalan Transaksi Penjualan mengembalikan stok batch FIFO & membatalkan piutang
     */
    public function test_void_sale_reverses_fifo_batch_and_cancels_receivable(): void
    {
        $posService = app(PosSaleService::class);

        $product = Product::create([
            'sku' => 'ITEM-VOID-01',
            'name' => 'Saklar Broco Single',
            'slug' => 'saklar-broco-single',
            'category_id' => $this->category->id,
            'base_unit_id' => $this->pcsUnit->id,
            'buy_price' => 12000,
            'selling_price' => 18000,
            'min_stock' => 5,
            'warehouse_location_id' => $this->location->id,
            'is_active' => true,
        ]);

        $batch = InventoryBatch::create([
            'batch_number' => 'BATCH-VOID-01',
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'initial_quantity' => 50,
            'available_quantity' => 50,
            'cost_price' => 12000,
            'received_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        // Jual 20 unit
        $cartData = [
            'payment_method' => 'cash',
            'paid_amount' => 360000,
            'items' => [
                [
                    'product_id' => $product->id,
                    'unit_id' => $this->pcsUnit->id,
                    'quantity' => 20,
                    'unit_price' => 18000,
                    'discount_amount' => 0,
                ],
            ],
        ];

        $sale = $posService->createSale($cartData, $this->cashier);

        // Batch available quantity drops to 30
        $batch->refresh();
        $this->assertEquals(30, $batch->available_quantity);
        $this->assertEquals(30, $product->fresh()->current_stock);

        // Sekarang batalkan transaksi (Void)
        $posService->voidSale($sale, $this->superAdmin, 'Customer membatalkan pesanan di kasir');

        // Verify status
        $sale->refresh();
        $this->assertEquals('void', $sale->status);

        // Verify FIFO Batch is fully restored back to 50!
        $batch->refresh();
        $this->assertEquals(50, $batch->available_quantity);
        $this->assertEquals('active', $batch->status);
        $this->assertEquals(50, $product->fresh()->current_stock);
    }

    /**
     * TEST 6: Role Based Access Control (RBAC): Kasir dilarang mengakses Settings Toko
     */
    public function test_rbac_cashier_cannot_access_settings(): void
    {
        // Kasir logged in
        $response = $this->actingAs($this->cashier)->get('/settings');
        $response->assertStatus(403);

        // Super Admin logged in
        $responseAdmin = $this->actingAs($this->superAdmin)->get('/settings');
        $responseAdmin->assertStatus(200);
    }

    /**
     * TEST 7: Pencegahan Stok Negatif: Jual Melebihi Stok Ditolak & Transaksi di-Rollback
     */
    public function test_prevent_overselling_throws_exception_and_leaves_stock_intact(): void
    {
        $posService = app(PosSaleService::class);

        $product = Product::create([
            'sku' => 'CAT-TEST-01',
            'name' => 'Cat Tembok Putih 5kg',
            'slug' => 'cat-tembok-putih-5kg',
            'category_id' => $this->category->id,
            'base_unit_id' => $this->pcsUnit->id,
            'buy_price' => 100000,
            'selling_price' => 135000,
            'min_stock' => 2,
            'warehouse_location_id' => $this->location->id,
            'is_active' => true,
        ]);

        // Only 5 items available in batch
        InventoryBatch::create([
            'batch_number' => 'BATCH-CAT-TEST',
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'initial_quantity' => 5,
            'available_quantity' => 5,
            'cost_price' => 100000,
            'received_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        $this->assertEquals(5, $product->fresh()->current_stock);

        // Try to buy 8 items (exceeds available 5)
        $cartData = [
            'payment_method' => 'cash',
            'paid_amount' => 1080000,
            'items' => [
                [
                    'product_id' => $product->id,
                    'unit_id' => $this->pcsUnit->id,
                    'quantity' => 8,
                    'unit_price' => 135000,
                    'discount_amount' => 0,
                ],
            ],
        ];

        $caughtException = false;
        try {
            $posService->createSale($cartData, $this->cashier);
        } catch (Exception $e) {
            $caughtException = true;
            $this->assertStringContainsString('Stok tidak mencukupi', $e->getMessage());
        }

        $this->assertTrue($caughtException, 'Expected Exception was not thrown when overselling');

        // Verify stock is still 5 and no sale record exists
        $this->assertEquals(5, $product->fresh()->current_stock);
        $this->assertEquals(0, Sale::count());
    }

    /**
     * TEST 8: Dashboard & Product Reports aggregate top products without ambiguous column SQL errors
     */
    public function test_dashboard_and_product_reports_query_without_ambiguous_column_error(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/dashboard');
        $response->assertStatus(200);

        $responseReport = $this->actingAs($this->superAdmin)->get('/reports/products');
        $responseReport->assertStatus(200);
    }

    /**
     * TEST 9: Wholesale and packaging units (GROSS, LUSIN, KG, LITER, DUS, etc.) are available and functional
     */
    public function test_standard_units_gross_lusin_kg_liter_dus_are_available_and_convert_correctly(): void
    {
        $gross = Unit::firstOrCreate(['code' => 'GROSS'], ['name' => 'Gross (144 Pcs / 12 Lusin)']);
        $lusin = Unit::firstOrCreate(['code' => 'LUSIN'], ['name' => 'Lusin (12 Pcs)']);
        $dus = Unit::firstOrCreate(['code' => 'DUS'], ['name' => 'Karton / Dus']);
        $kg = Unit::firstOrCreate(['code' => 'KG'], ['name' => 'Kilogram']);
        $liter = Unit::firstOrCreate(['code' => 'LITER'], ['name' => 'Liter']);

        $product = Product::create([
            'sku' => 'TEST-CANDLE-01',
            'barcode' => '8999999000111',
            'name' => 'Lilin Ulang Tahun Spiral Ulir',
            'slug' => 'lilin-ulang-tahun-spiral-ulir',
            'category_id' => $this->category->id,
            'base_unit_id' => $this->pcsUnit->id,
            'purchase_unit_id' => $gross->id,
            'selling_unit_id' => $this->pcsUnit->id,
            'buy_price' => 500,
            'selling_price' => 1000,
            'wholesale_price' => 800,
            'min_stock' => 50,
            'is_active' => true,
        ]);

        // 1 Lusin = 12 Pcs
        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $lusin->id,
            'conversion_factor' => 12,
            'buy_price' => 5500,
            'selling_price' => 10000,
            'wholesale_price' => 8500,
        ]);

        // 1 Gross = 144 Pcs (12 Lusin)
        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $gross->id,
            'conversion_factor' => 144,
            'buy_price' => 60000,
            'selling_price' => 110000,
            'wholesale_price' => 95000,
        ]);

        $conversionService = app(UnitConversionService::class);

        // Convert 2 Lusin to PCS -> 24 PCS
        $baseFromLusin = $conversionService->convertToBase($product, $lusin->id, 2);
        $this->assertEquals(24.0, $baseFromLusin);

        // Convert 3 Gross to PCS -> 432 PCS
        $baseFromGross = $conversionService->convertToBase($product, $gross->id, 3);
        $this->assertEquals(432.0, $baseFromGross);

        // Verify master units listing page responds OK
        $response = $this->actingAs($this->superAdmin)->get('/master/units');
        $response->assertStatus(200);
        $response->assertSee('GROSS');
        $response->assertSee('LUSIN');
    }

    /**
     * TEST 10: Manual Sales Transaction Entry and Unified Sales Listing (without POS counter)
     */
    public function test_manual_sales_entry_and_unified_sales_listing(): void
    {
        $lusin = Unit::firstOrCreate(['code' => 'LUSIN'], ['name' => 'Lusin (12 Pcs)']);

        // Create product with inventory batch
        $product = Product::create([
            'sku' => 'TPG-CAKRA-TEST',
            'barcode' => '8999999123456',
            'name' => 'Tepung Cakra Kembar Premium',
            'slug' => 'tepung-cakra-kembar-premium',
            'category_id' => $this->category->id,
            'base_unit_id' => $this->pcsUnit->id,
            'purchase_unit_id' => $this->packUnit->id,
            'selling_unit_id' => $this->pcsUnit->id,
            'buy_price' => 10000,
            'selling_price' => 12500,
            'wholesale_price' => 11500,
            'min_stock' => 10,
            'is_active' => true,
        ]);

        InventoryBatch::create([
            'batch_number' => 'BATCH-TEST-SLS-01',
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'initial_quantity' => 100,
            'available_quantity' => 100,
            'cost_price' => 10000,
            'received_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        $customer = Customer::create([
            'code' => 'CUST-TEST-01',
            'name' => 'Ibu Rahma Bakery',
            'category' => 'pelanggan_tetap',
            'phone' => '08123456789',
        ]);

        // 1. Visit GET /transactions/sales/create
        $responseCreate = $this->actingAs($this->superAdmin)->get('/transactions/sales/create');
        $responseCreate->assertStatus(200);
        $responseCreate->assertSee('Catat Transaksi Penjualan');

        // 2. Submit POST /transactions/sales (Manual sales entry)
        $payload = [
            'sale_date' => now()->toDateString(),
            'customer_id' => $customer->id,
            'price_type' => 'retail',
            'payment_method' => 'cash',
            'paid_amount' => 50000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'notes' => 'Pesanan kue via WhatsApp Ibu Rahma',
            'items' => [
                [
                    'product_id' => $product->id,
                    'unit_id' => $this->pcsUnit->id,
                    'quantity' => 4,
                    'unit_price' => 12500,
                    'discount_amount' => 0,
                ],
            ],
        ];

        $responsePost = $this->actingAs($this->superAdmin)->post('/transactions/sales', $payload);
        $responsePost->assertRedirect();

        $sale = Sale::where('customer_id', $customer->id)->latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(50000, $sale->total_amount);
        $this->assertEquals('paid', $sale->payment_status);

        // Verify stock deducted from 100 to 96
        $this->assertEquals(96, $product->fresh()->current_stock);

        // 3. Visit GET /transactions/sales and check that the manual sale appears
        $responseIndex = $this->actingAs($this->superAdmin)->get('/transactions/sales');
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee($sale->invoice_number);
        $responseIndex->assertSee('Ibu Rahma Bakery');

        // 4. Visit GET /transactions/sales/{sale} and print
        $responseShow = $this->actingAs($this->superAdmin)->get("/transactions/sales/{$sale->id}");
        $responseShow->assertStatus(200);
        $responseShow->assertSee($sale->invoice_number);

        $responsePrint = $this->actingAs($this->superAdmin)->get("/transactions/sales/{$sale->id}/print");
        $responsePrint->assertStatus(200);
        $responsePrint->assertSee('FAKTUR PENJUALAN');

        // 5. Test sale without customer (walk-in) succeeds as paid without requiring customer
        $payloadWalkIn = [
            'sale_date' => now()->toDateString(),
            'customer_id' => null,
            'price_type' => 'retail',
            'payment_method' => 'cash',
            'paid_amount' => 12500,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'notes' => 'Catatan penjualan eceran walk-in tanpa customer khusus',
            'items' => [
                [
                    'product_id' => $product->id,
                    'unit_id' => $this->pcsUnit->id,
                    'quantity' => 1,
                    'unit_price' => 12500,
                    'discount_amount' => 0,
                ],
            ],
        ];

        $resWalkIn = $this->actingAs($this->superAdmin)->post('/transactions/sales', $payloadWalkIn);
        $resWalkIn->assertRedirect();

        $saleWalkIn = Sale::where('notes', 'Catatan penjualan eceran walk-in tanpa customer khusus')->first();
        $this->assertNotNull($saleWalkIn);
        $this->assertEquals(12500, $saleWalkIn->total_amount);
        $this->assertEquals('paid', $saleWalkIn->payment_status);
        $this->assertNull($saleWalkIn->receivable);
    }

    /**
     * TEST 11: Create product with photo up to 10MB and proper validation error handling
     */
    public function test_create_product_with_image_and_validation(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        // 1. Test image > 10MB fails validation
        $oversizedImage = \Illuminate\Http\UploadedFile::fake()->image('large.jpg')->size(11000); // 11 MB
        $responseFail = $this->actingAs($this->superAdmin)->post('/master/products', [
            'sku' => 'TPG-FAIL-01',
            'name' => 'Produk Gambar Kebesaran',
            'category_id' => $this->category->id,
            'base_unit_id' => $this->pcsUnit->id,
            'buy_price' => 10000,
            'selling_price' => 12000,
            'image' => $oversizedImage,
        ]);
        $responseFail->assertSessionHasErrors('image');

        // 2. Test image 4MB (which failed in 2MB limit) now succeeds with 10MB limit
        $validImage = \Illuminate\Http\UploadedFile::fake()->image('good_photo.jpg')->size(4096); // 4 MB
        $responseSuccess = $this->actingAs($this->superAdmin)->post('/master/products', [
            'sku' => 'TPG-GOOD-01',
            'name' => 'Tepung Terigu Foto 4MB',
            'category_id' => $this->category->id,
            'base_unit_id' => $this->pcsUnit->id,
            'buy_price' => 10000,
            'selling_price' => 12000,
            'wholesale_price' => 11000,
            'min_stock' => 10,
            'weight' => 1000,
            'image' => $validImage,
        ]);
        $responseSuccess->assertRedirect('/master/products');

        $created = Product::where('sku', 'TPG-GOOD-01')->first();
        $this->assertNotNull($created);
        $this->assertNotNull($created->image);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($created->image);
    }

    /**
     * TEST 12: Create product with initial physical stock & Quick add stock via Master Produk
     */
    public function test_create_product_with_initial_physical_stock_and_quick_add_stock(): void
    {
        // 1. Create product with initial stock = 25 PCS
        $response = $this->actingAs($this->superAdmin)->post('/master/products', [
            'sku' => 'INIT-STK-01',
            'name' => 'Produk Dengan Saldo Stok Awal',
            'category_id' => $this->category->id,
            'base_unit_id' => $this->pcsUnit->id,
            'buy_price' => 5000,
            'selling_price' => 6500,
            'wholesale_price' => 6000,
            'min_stock' => 10,
            'initial_stock' => 25,
            'initial_warehouse_id' => $this->warehouse->id,
        ]);
        $response->assertRedirect('/master/products');

        $product = Product::where('sku', 'INIT-STK-01')->first();
        $this->assertNotNull($product);
        $this->assertEquals(25, $product->current_stock);
        $this->assertStringContainsString('25', $product->stock_formatted);

        $batch = InventoryBatch::where('product_id', $product->id)->first();
        $this->assertNotNull($batch);
        $this->assertEquals(25, $batch->available_quantity);
        $this->assertEquals('active', $batch->status);

        $movement = StockMovement::where('product_id', $product->id)->where('type', 'initial_stock')->first();
        $this->assertNotNull($movement);
        $this->assertEquals(25, $movement->quantity);

        // 2. Quick add stock via POST /master/products/{product}/stock (+15 PCS)
        $responseAddStock = $this->actingAs($this->superAdmin)->post("/master/products/{$product->id}/stock", [
            'quantity' => 15,
            'warehouse_id' => $this->warehouse->id,
            'notes' => 'Tambahan stok fisik rak depan',
        ]);
        $responseAddStock->assertRedirect();
        $responseAddStock->assertSessionHas('success');

        $this->assertEquals(40, $product->fresh()->current_stock);
        $this->assertStringContainsString('40', $product->fresh()->stock_formatted);

        // 3. Test listing and show page displays stock
        $responseIndex = $this->actingAs($this->superAdmin)->get('/master/products');
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('INIT-STK-01');
        $responseIndex->assertSee('40');

        $responseShow = $this->actingAs($this->superAdmin)->get("/master/products/{$product->id}");
        $responseShow->assertStatus(200);
        $responseShow->assertSee('40 PCS');
    }

    public function test_update_sale_payment_status_to_paid_and_dp(): void
    {
        $customer = Customer::create([
            'code' => 'CUST-TEST-PAY',
            'name' => 'Bpk. Ahmad Santoso',
            'phone' => '081233445566',
            'category' => 'retail',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'TEST-PAY-01',
            'name' => 'Tepung Segitiga Biru 1kg',
            'category_id' => $this->category->id,
            'base_unit_id' => $this->pcsUnit->id,
            'cost_price' => 10000,
            'retail_price' => 15000,
            'wholesale_price' => 14000,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        InventoryBatch::create([
            'batch_number' => 'BATCH-TEST-PAY-01',
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'warehouse_location_id' => $this->location->id,
            'initial_quantity' => 50,
            'available_quantity' => 50,
            'cost_price' => 10000,
            'received_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        // 1. Create a sale with DP (Total: 60.000, Paid: 20.000, Remaining: 40.000)
        $saleResponse = $this->actingAs($this->superAdmin)->post('/pos/checkout', [
            'customer_id' => $customer->id,
            'price_type' => 'retail',
            'payment_method' => 'dp',
            'paid_amount' => 20000,
            'items' => [
                [
                    'product_id' => $product->id,
                    'unit_id' => $this->pcsUnit->id,
                    'quantity' => 4,
                    'unit_price' => 15000,
                    'subtotal' => 60000,
                ]
            ]
        ]);
        $saleResponse->assertStatus(200);

        $sale = Sale::latest()->first();
        $this->assertEquals('dp', $sale->payment_status);
        $this->assertEquals(20000, $sale->paid_amount);
        $this->assertEquals(60000, $sale->total_amount);
        $this->assertNotNull($sale->receivable);
        $this->assertEquals(40000, $sale->receivable->remaining_balance);

        // 2. Update payment status to LUNAS via updatePayment route
        $updateResponse = $this->actingAs($this->superAdmin)->post("/transactions/sales/{$sale->id}/payment", [
            'payment_status' => 'paid',
            'amount' => 40000,
            'payment_method' => 'transfer',
            'notes' => 'Pelunasan transfer BCA',
        ]);
        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('success');

        $sale->refresh();
        $this->assertEquals('paid', $sale->payment_status);
        $this->assertEquals(60000, $sale->paid_amount);
        $this->assertEquals('transfer', $sale->payment_method);
        $this->assertEquals(0, $sale->receivable->fresh()->remaining_balance);
        $this->assertEquals('paid', $sale->receivable->fresh()->status);

        // Verify Payment and CashTransaction recorded
        $this->assertDatabaseHas('payments', [
            'reference_id' => $sale->id,
            'amount' => 40000,
            'payment_method' => 'transfer',
        ]);
        $this->assertDatabaseHas('cash_transactions', [
            'amount' => 40000,
            'category' => 'Pelunasan Piutang',
        ]);
    }

    public function test_profile_photo_upload_and_max_mb_setting(): void
    {
        Storage::fake('public');

        // 1. Visit GET /profile
        $response = $this->actingAs($this->superAdmin)->get('/profile');
        $response->assertStatus(200);
        $response->assertSee('Profil Pengguna');
        $response->assertSee('Unggah Foto');

        // 2. Set max upload size to 3 MB via Settings
        $settingResponse = $this->actingAs($this->superAdmin)->put('/settings', [
            'max_upload_size_mb' => 3,
            'store_name' => 'TOKO SUMBER BERKAH',
        ]);
        $settingResponse->assertRedirect();
        $this->assertEquals(3, \App\Models\Setting::get('max_upload_size_mb'));

        // 3. Upload a valid avatar within 3 MB (e.g. 500 KB)
        $validAvatar = \Illuminate\Http\UploadedFile::fake()->image('my-photo.jpg', 400, 400)->size(500);
        $uploadResponse = $this->actingAs($this->superAdmin)->put('/profile', [
            'name' => 'Super Administrator Edit',
            'phone' => '081299998888',
            'avatar' => $validAvatar,
        ]);
        $uploadResponse->assertRedirect();
        $uploadResponse->assertSessionHas('success');

        $this->superAdmin->refresh();
        $this->assertEquals('Super Administrator Edit', $this->superAdmin->name);
        $this->assertNotNull($this->superAdmin->avatar);
        Storage::disk('public')->assertExists($this->superAdmin->avatar);

        // 4. Attempt upload exceeding 3 MB (e.g. 4000 KB) -> Should fail validation
        $oversizedAvatar = \Illuminate\Http\UploadedFile::fake()->image('big-photo.jpg', 1200, 1200)->size(4000);
        $failResponse = $this->actingAs($this->superAdmin)->put('/profile', [
            'name' => 'Super Administrator Edit',
            'avatar' => $oversizedAvatar,
        ]);
        $failResponse->assertSessionHasErrors('avatar');

        // 5. Remove avatar
        $removeResponse = $this->actingAs($this->superAdmin)->put('/profile', [
            'name' => 'Super Administrator Edit',
            'remove_avatar' => '1',
        ]);
        $removeResponse->assertRedirect();
        $removeResponse->assertSessionHas('success');

        $this->superAdmin->refresh();
        $this->assertNull($this->superAdmin->avatar);
    }
}
