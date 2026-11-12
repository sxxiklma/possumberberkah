<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $categoryId = $request->get('category_id');
        $brandId = $request->get('brand_id');
        $stockFilter = $request->get('stock_filter'); // all, low, out

        $query = Product::with(['category', 'brand', 'baseUnit', 'purchaseUnit', 'location.warehouse', 'productUnits.unit', 'batches']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($brandId) {
            $query->where('brand_id', $brandId);
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        // Stock filter in memory if requested
        if ($stockFilter === 'low') {
            $products->setCollection($products->getCollection()->filter(fn($p) => $p->current_stock <= $p->min_stock && $p->current_stock > 0));
        } elseif ($stockFilter === 'out') {
            $products->setCollection($products->getCollection()->filter(fn($p) => $p->current_stock <= 0));
        }

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $locations = WarehouseLocation::with('warehouse')->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('master.products.index', compact('products', 'categories', 'brands', 'units', 'locations', 'warehouses', 'search', 'categoryId', 'brandId', 'stockFilter'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $locations = WarehouseLocation::with('warehouse')->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('master.products.create', compact('categories', 'brands', 'units', 'locations', 'warehouses'));
    }

    public function store(Request $request)
    {
        $messages = [
            'sku.required' => 'SKU produk wajib diisi.',
            'sku.unique' => 'SKU produk sudah terdaftar untuk produk lain.',
            'barcode.unique' => 'Barcode sudah terdaftar untuk produk lain.',
            'name.required' => 'Nama produk lengkap wajib diisi.',
            'category_id.required' => 'Kategori produk wajib dipilih.',
            'category_id.exists' => 'Kategori produk yang dipilih tidak ditemukan.',
            'base_unit_id.required' => 'Satuan dasar terkecil (Base Unit) wajib dipilih.',
            'base_unit_id.exists' => 'Satuan dasar yang dipilih tidak valid.',
            'buy_price.required' => 'Harga beli / modal dasar wajib diisi.',
            'buy_price.min' => 'Harga beli / modal dasar tidak boleh bernilai negatif.',
            'selling_price.required' => 'Harga jual retail dasar wajib diisi.',
            'selling_price.min' => 'Harga jual retail dasar tidak boleh bernilai negatif.',
            'image.image' => 'File yang dipilih harus berupa file gambar valid.',
            'image.mimes' => 'Format file foto yang didukung: JPG, JPEG, PNG, WEBP, atau GIF.',
            'image.max' => 'Ukuran file foto melebihi batas maksimal 10 MB.',
        ];

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'max:50', 'unique:products,barcode'],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'base_unit_id' => ['required', 'exists:units,id'],
            'purchase_unit_id' => ['nullable', 'exists:units,id'],
            'selling_unit_id' => ['nullable', 'exists:units,id'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'warehouse_location_id' => ['nullable', 'exists:warehouse_locations,id'],
            'description' => ['nullable', 'string'],
            'has_expiry' => ['nullable', 'boolean'],
            'expiry_date' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:10240'], // Maksimal 10 MB
            // Secondary packaging units
            'units' => ['nullable', 'array'],
            'units.*.unit_id' => ['required_with:units', 'exists:units,id'],
            'units.*.conversion_factor' => ['required_with:units', 'numeric', 'gt:0'],
            'units.*.buy_price' => ['nullable', 'numeric', 'min:0'],
            'units.*.selling_price' => ['nullable', 'numeric', 'min:0'],
            'units.*.wholesale_price' => ['nullable', 'numeric', 'min:0'],
            // Initial stock (Saldo Stok Awal Fisik)
            'initial_stock' => ['nullable', 'numeric', 'min:0'],
            'initial_stock_unit_id' => ['nullable', 'exists:units,id'],
            'initial_warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'initial_batch_number' => ['nullable', 'string', 'max:100'],
            'initial_expiry_date' => ['nullable', 'date'],
        ], $messages);

        try {
            return DB::transaction(function () use ($request, $validated) {
                $imagePath = null;
                if ($request->hasFile('image')) {
                    $imagePath = $request->file('image')->store('products', 'public');
                }

                $barcode = !empty($validated['barcode']) ? trim($validated['barcode']) : null;
                $brandId = !empty($validated['brand_id']) ? $validated['brand_id'] : null;
                $locationId = !empty($validated['warehouse_location_id']) ? $validated['warehouse_location_id'] : null;

                $inputExpiryDate = $request->input('expiry_date') ?: $request->input('initial_expiry_date');
                $hasExpiry = $request->boolean('has_expiry') || !empty($inputExpiryDate);

                $product = Product::create([
                    'sku' => strtoupper(trim($validated['sku'])),
                    'barcode' => $barcode,
                    'name' => trim($validated['name']),
                    'slug' => Str::slug($validated['name']),
                    'image' => $imagePath,
                    'category_id' => $validated['category_id'],
                    'brand_id' => $brandId,
                    'description' => $validated['description'] ?? null,
                    'base_unit_id' => $validated['base_unit_id'],
                    'purchase_unit_id' => $validated['purchase_unit_id'] ?? $validated['base_unit_id'],
                    'selling_unit_id' => $validated['selling_unit_id'] ?? $validated['base_unit_id'],
                    'buy_price' => $validated['buy_price'],
                    'selling_price' => $validated['selling_price'],
                    'wholesale_price' => $validated['wholesale_price'] ?? $validated['selling_price'],
                    'min_stock' => $validated['min_stock'] ?? 10,
                    'weight' => $validated['weight'] ?? 0,
                    'warehouse_location_id' => $locationId,
                    'has_expiry' => $hasExpiry,
                    'expiry_date' => $inputExpiryDate ?: null,
                    'is_active' => true,
                ]);

                // Save secondary units (Multi-unit)
                if (!empty($validated['units'])) {
                    foreach ($validated['units'] as $u) {
                        if ($u['unit_id'] != $product->base_unit_id) {
                            $factor = (float) ($u['conversion_factor'] ?: 1);
                            $baseBuy = (float) $product->buy_price;
                            $baseSell = (float) $product->selling_price;
                            $baseWs = (float) ($product->wholesale_price ?: $product->selling_price);

                            $calcBuy = round($baseBuy * $factor, 2);
                            $calcSell = round($baseSell * $factor, 2);
                            $calcWs = round($baseWs * $factor, 2);

                            $uBuy = !empty($u['buy_price']) ? (float)$u['buy_price'] : 0;
                            $uSell = !empty($u['selling_price']) ? (float)$u['selling_price'] : 0;
                            $uWs = !empty($u['wholesale_price']) ? (float)$u['wholesale_price'] : 0;

                            $finalBuy = ($uBuy > 0 && ($factor <= 1 || $uBuy > $baseBuy)) ? $uBuy : $calcBuy;
                            $finalSell = ($uSell > 0 && ($factor <= 1 || $uSell > $baseSell)) ? $uSell : $calcSell;
                            $finalWs = ($uWs > 0 && ($factor <= 1 || $uWs > $baseWs)) ? $uWs : $calcWs;

                            ProductUnit::updateOrCreate(
                                ['product_id' => $product->id, 'unit_id' => $u['unit_id']],
                                [
                                    'conversion_factor' => $factor,
                                    'buy_price' => $finalBuy,
                                    'selling_price' => $finalSell,
                                    'wholesale_price' => $finalWs,
                                    'is_base' => false,
                                ]
                            );
                        }
                    }
                }

                // Saldo Stok Awal Fisik (jika diisi > 0)
                $initialStockInput = isset($validated['initial_stock']) ? (float)$validated['initial_stock'] : 0;
                if ($initialStockInput > 0) {
                    $selectedUnitId = $request->input('initial_stock_unit_id');
                    $conversionFactor = 1.0;
                    $unitCode = $product->baseUnit?->code ?? 'PCS';

                    if ($selectedUnitId && $selectedUnitId != $product->base_unit_id) {
                        $secondaryUnit = ProductUnit::with('unit')->where('product_id', $product->id)->where('unit_id', $selectedUnitId)->first();
                        if ($secondaryUnit && $secondaryUnit->conversion_factor > 0) {
                            $conversionFactor = (float) $secondaryUnit->conversion_factor;
                            $unitCode = $secondaryUnit->unit?->code ?? $unitCode;
                        }
                    }

                    // Kuantitas riil persediaan dalam satuan dasar terkecil
                    $initialStockBase = $initialStockInput * $conversionFactor;

                    $whId = !empty($validated['initial_warehouse_id']) ? $validated['initial_warehouse_id'] : null;
                    if (!$whId && $locationId) {
                        $loc = WarehouseLocation::find($locationId);
                        $whId = $loc?->warehouse_id;
                    }
                    if (!$whId) {
                        $whId = Warehouse::where('is_active', true)->first()?->id ?? 1;
                    }

                    $batchNum = !empty($validated['initial_batch_number']) 
                        ? trim($validated['initial_batch_number']) 
                        : 'BATCH-' . $product->sku . '-' . date('ymd') . '-01';
                    $expiryDate = $inputExpiryDate ?: (!empty($validated['initial_expiry_date']) ? $validated['initial_expiry_date'] : null);

                    $batch = InventoryBatch::create([
                        'batch_number' => $batchNum,
                        'product_id' => $product->id,
                        'warehouse_id' => $whId,
                        'warehouse_location_id' => $locationId,
                        'initial_quantity' => $initialStockBase,
                        'available_quantity' => $initialStockBase,
                        'cost_price' => $product->buy_price,
                        'received_date' => now()->toDateString(),
                        'expiry_date' => $expiryDate ?: ($product->has_expiry ? now()->addMonths(12)->toDateString() : null),
                        'status' => 'active',
                    ]);

                    $noteDetail = $conversionFactor > 1 
                        ? "Saldo awal stok fisik {$initialStockInput} {$unitCode} (@ {$conversionFactor} " . ($product->baseUnit?->code ?? '') . " = {$initialStockBase} " . ($product->baseUnit?->code ?? '') . ")"
                        : "Saldo awal stok fisik {$initialStockBase} " . ($product->baseUnit?->code ?? '');

                    StockMovement::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $whId,
                        'inventory_batch_id' => $batch->id,
                        'type' => 'initial_stock',
                        'reference_number' => 'INIT-' . strtoupper(Str::random(6)),
                        'quantity' => $initialStockBase,
                        'quantity_before' => 0,
                        'quantity_after' => $initialStockBase,
                        'unit_cost' => $product->buy_price,
                        'total_cost' => $initialStockBase * $product->buy_price,
                        'user_id' => auth()->id() ?? User::first()?->id ?? 1,
                        'notes' => $noteDetail,
                    ]);
                }

                ActivityLog::record(
                    action: 'create_product',
                    module: 'Master Data',
                    description: "Menambahkan produk baru: {$product->name} (SKU: {$product->sku})",
                    properties: ['product_id' => $product->id, 'sku' => $product->sku]
                );

                return redirect()->route('master.products.index')
                    ->with('success', "Produk {$product->name} berhasil ditambahkan!");
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', "Gagal menyimpan produk baru: " . $e->getMessage());
        }
    }

    public function show(Product $product)
    {
        $product->load([
            'category',
            'brand',
            'baseUnit',
            'purchaseUnit',
            'sellingUnit',
            'location.warehouse',
            'productUnits.unit',
            'activeBatches',
            'stockMovements.user' => fn($q) => $q->latest()->take(10)
        ]);

        $warehouses = Warehouse::where('is_active', true)->get();

        return view('master.products.show', compact('product', 'warehouses'));
    }

    public function edit(Product $product)
    {
        $product->load(['productUnits.unit']);
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $locations = WarehouseLocation::with('warehouse')->get();

        return view('master.products.edit', compact('product', 'categories', 'brands', 'units', 'locations'));
    }

    public function update(Request $request, Product $product)
    {
        $messages = [
            'sku.required' => 'SKU produk wajib diisi.',
            'sku.unique' => 'SKU produk sudah terdaftar untuk produk lain.',
            'barcode.unique' => 'Barcode sudah terdaftar untuk produk lain.',
            'name.required' => 'Nama produk lengkap wajib diisi.',
            'category_id.required' => 'Kategori produk wajib dipilih.',
            'category_id.exists' => 'Kategori produk yang dipilih tidak ditemukan.',
            'base_unit_id.required' => 'Satuan dasar terkecil (Base Unit) wajib dipilih.',
            'base_unit_id.exists' => 'Satuan dasar yang dipilih tidak valid.',
            'buy_price.required' => 'Harga beli / modal dasar wajib diisi.',
            'buy_price.min' => 'Harga beli / modal dasar tidak boleh bernilai negatif.',
            'selling_price.required' => 'Harga jual retail dasar wajib diisi.',
            'selling_price.min' => 'Harga jual retail dasar tidak boleh bernilai negatif.',
            'image.image' => 'File yang dipilih harus berupa file gambar valid.',
            'image.mimes' => 'Format file foto yang didukung: JPG, JPEG, PNG, WEBP, atau GIF.',
            'image.max' => 'Ukuran file foto melebihi batas maksimal 10 MB.',
        ];

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku,' . $product->id],
            'barcode' => ['nullable', 'string', 'max:50', 'unique:products,barcode,' . $product->id],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'base_unit_id' => ['required', 'exists:units,id'],
            'purchase_unit_id' => ['nullable', 'exists:units,id'],
            'selling_unit_id' => ['nullable', 'exists:units,id'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'warehouse_location_id' => ['nullable', 'exists:warehouse_locations,id'],
            'description' => ['nullable', 'string'],
            'has_expiry' => ['nullable', 'boolean'],
            'expiry_date' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:10240'], // Maksimal 10 MB
            'units' => ['nullable', 'array'],
            'units.*.unit_id' => ['required_with:units', 'exists:units,id'],
            'units.*.conversion_factor' => ['required_with:units', 'numeric', 'gt:0'],
            'units.*.buy_price' => ['nullable', 'numeric', 'min:0'],
            'units.*.selling_price' => ['nullable', 'numeric', 'min:0'],
            'units.*.wholesale_price' => ['nullable', 'numeric', 'min:0'],
        ], $messages);

        try {
            return DB::transaction(function () use ($request, $validated, $product) {
                $imagePath = $product->image;
                if ($request->hasFile('image')) {
                    if ($product->image) {
                        Storage::disk('public')->delete($product->image);
                    }
                    $imagePath = $request->file('image')->store('products', 'public');
                }

                $barcode = !empty($validated['barcode']) ? trim($validated['barcode']) : null;
                $brandId = !empty($validated['brand_id']) ? $validated['brand_id'] : null;
                $locationId = !empty($validated['warehouse_location_id']) ? $validated['warehouse_location_id'] : null;

                $inputExpiryDate = $request->input('expiry_date');
                $hasExpiry = $request->boolean('has_expiry') || !empty($inputExpiryDate);

                $product->update([
                    'sku' => strtoupper(trim($validated['sku'])),
                    'barcode' => $barcode,
                    'name' => trim($validated['name']),
                    'slug' => Str::slug($validated['name']),
                    'image' => $imagePath,
                    'category_id' => $validated['category_id'],
                    'brand_id' => $brandId,
                    'description' => $validated['description'] ?? null,
                    'base_unit_id' => $validated['base_unit_id'],
                    'purchase_unit_id' => $validated['purchase_unit_id'] ?? $validated['base_unit_id'],
                    'selling_unit_id' => $validated['selling_unit_id'] ?? $validated['base_unit_id'],
                    'buy_price' => $validated['buy_price'],
                    'selling_price' => $validated['selling_price'],
                    'wholesale_price' => $validated['wholesale_price'] ?? $validated['selling_price'],
                    'min_stock' => $validated['min_stock'] ?? 10,
                    'weight' => $validated['weight'] ?? 0,
                    'warehouse_location_id' => $locationId,
                    'has_expiry' => $hasExpiry,
                    'expiry_date' => $inputExpiryDate ?: null,
                    'is_active' => $request->boolean('is_active', true),
                ]);

                // Sync secondary units
                $product->productUnits()->delete();
                if (!empty($validated['units'])) {
                    foreach ($validated['units'] as $u) {
                        if ($u['unit_id'] != $product->base_unit_id) {
                            $factor = (float) ($u['conversion_factor'] ?: 1);
                            $baseBuy = (float) $product->buy_price;
                            $baseSell = (float) $product->selling_price;
                            $baseWs = (float) ($product->wholesale_price ?: $product->selling_price);

                            $calcBuy = round($baseBuy * $factor, 2);
                            $calcSell = round($baseSell * $factor, 2);
                            $calcWs = round($baseWs * $factor, 2);

                            $uBuy = !empty($u['buy_price']) ? (float)$u['buy_price'] : 0;
                            $uSell = !empty($u['selling_price']) ? (float)$u['selling_price'] : 0;
                            $uWs = !empty($u['wholesale_price']) ? (float)$u['wholesale_price'] : 0;

                            $finalBuy = ($uBuy > 0 && ($factor <= 1 || $uBuy > $baseBuy)) ? $uBuy : $calcBuy;
                            $finalSell = ($uSell > 0 && ($factor <= 1 || $uSell > $baseSell)) ? $uSell : $calcSell;
                            $finalWs = ($uWs > 0 && ($factor <= 1 || $uWs > $baseWs)) ? $uWs : $calcWs;

                            ProductUnit::updateOrCreate(
                                ['product_id' => $product->id, 'unit_id' => $u['unit_id']],
                                [
                                    'conversion_factor' => $factor,
                                    'buy_price' => $finalBuy,
                                    'selling_price' => $finalSell,
                                    'wholesale_price' => $finalWs,
                                    'is_base' => false,
                                ]
                            );
                        }
                    }
                }

                ActivityLog::record(
                    action: 'update_product',
                    module: 'Master Data',
                    description: "Memperbarui produk: {$product->name} (SKU: {$product->sku})",
                    properties: ['product_id' => $product->id]
                );

                return redirect()->route('master.products.index')
                    ->with('success', "Produk {$product->name} berhasil diperbarui!");
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', "Gagal memperbarui produk: " . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        // Safe soft-delete
        $product->delete();

        ActivityLog::record(
            action: 'delete_product',
            module: 'Master Data',
            description: "Menghapus produk: {$product->name} (SKU: {$product->sku})"
        );

        return redirect()->route('master.products.index')
            ->with('success', "Produk {$product->name} berhasil dihapus.");
    }

    /**
     * Download CSV/Excel Template for product import
     */
    public function exportTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_produk_sumber_berkah.csv"',
        ];

        $columns = [
            'SKU', 'Barcode', 'Nama Produk', 'Kode Kategori', 'Satuan Dasar',
            'Satuan Kemasan', 'Isi Per Kemasan', 'Harga Beli', 'Harga Jual Retail',
            'Harga Jual Grosir', 'Stok Minimum', 'Lokasi Rak', 'Masa Kedaluwarsa (1/0)'
        ];

        $sampleRows = [
            ['PKU-BAJA-07', '899123456010', 'Paku Baja Beton 7 cm', 'CAT-01', 'PCS', 'PACK', '100', '350', '500', '420', '100', 'GUD-A/RAK-01/B-01', '0'],
            ['CAT-DULUX-WT', '899123456011', 'Cat Dulux Weathershield Putih 2.5 L', 'CAT-02', 'BOTOL', 'DUS', '4', '210000', '265000', '245000', '10', 'GUD-A/RAK-02/B-02', '1'],
        ];

        $callback = function () use ($columns, $sampleRows) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);
            foreach ($sampleRows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import products from CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls'],
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $rows = array_map('str_getcsv', file($path));
        if (count($rows) <= 1) {
            return back()->with('error', 'File import kosong atau tidak memiliki baris data.');
        }

        // Header check
        $header = array_shift($rows);
        $importedCount = 0;
        $failedCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowNum = $index + 2;
                if (empty($row) || empty($row[0])) {
                    continue;
                }

                $sku = strtoupper(trim($row[0] ?? ''));
                $barcode = trim($row[1] ?? '') ?: null;
                $name = trim($row[2] ?? '');
                $categoryCode = trim($row[3] ?? '');
                $baseUnitCode = strtoupper(trim($row[4] ?? 'PCS'));
                $packUnitCode = strtoupper(trim($row[5] ?? ''));
                $packFactor = (float) ($row[6] ?? 1);
                $buyPrice = (float) ($row[7] ?? 0);
                $sellPrice = (float) ($row[8] ?? 0);
                $wholesalePrice = (float) ($row[9] ?? $sellPrice);
                $minStock = (float) ($row[10] ?? 10);
                $locationCode = trim($row[11] ?? '');
                $hasExpiry = (bool) ($row[12] ?? 0);

                if (empty($sku) || empty($name)) {
                    $errors[] = "Baris {$rowNum}: SKU dan Nama Produk wajib diisi.";
                    $failedCount++;
                    continue;
                }

                if (Product::where('sku', $sku)->exists()) {
                    $errors[] = "Baris {$rowNum}: SKU '{$sku}' sudah terdaftar.";
                    $failedCount++;
                    continue;
                }

                $category = Category::where('code', $categoryCode)->orWhere('name', 'like', "%{$categoryCode}%")->first();
                if (!$category) {
                    $category = Category::first();
                }

                $baseUnit = Unit::where('code', $baseUnitCode)->first();
                if (!$baseUnit) {
                    $baseUnit = Unit::firstOrCreate(['code' => $baseUnitCode], ['name' => $baseUnitCode]);
                }

                $location = WarehouseLocation::where('code', $locationCode)->first();

                $product = Product::create([
                    'sku' => $sku,
                    'barcode' => $barcode,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'category_id' => $category->id,
                    'base_unit_id' => $baseUnit->id,
                    'purchase_unit_id' => $baseUnit->id,
                    'selling_unit_id' => $baseUnit->id,
                    'buy_price' => $buyPrice,
                    'selling_price' => $sellPrice,
                    'wholesale_price' => $wholesalePrice,
                    'min_stock' => $minStock,
                    'warehouse_location_id' => $location?->id,
                    'has_expiry' => $hasExpiry,
                    'is_active' => true,
                ]);

                // Packaging unit conversion
                if (!empty($packUnitCode) && $packFactor > 1) {
                    $packUnit = Unit::where('code', $packUnitCode)->first();
                    if (!$packUnit) {
                        $packUnit = Unit::firstOrCreate(['code' => $packUnitCode], ['name' => $packUnitCode]);
                    }

                    ProductUnit::create([
                        'product_id' => $product->id,
                        'unit_id' => $packUnit->id,
                        'conversion_factor' => $packFactor,
                        'buy_price' => $buyPrice * $packFactor,
                        'selling_price' => $sellPrice * $packFactor,
                        'wholesale_price' => $wholesalePrice * $packFactor,
                        'is_base' => false,
                    ]);
                }

                $importedCount++;
            }

            DB::commit();

            ActivityLog::record(
                action: 'import_products',
                module: 'Master Data',
                description: "Import produk massal: {$importedCount} berhasil, {$failedCount} gagal."
            );

            $msg = "Import berhasil: {$importedCount} produk ditambahkan.";
            if ($failedCount > 0) {
                $msg .= " ({$failedCount} produk dilewati karena error/duplikasi).";
            }

            return back()->with('success', $msg)->with('import_errors', $errors);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses import: ' . $e->getMessage());
        }
    }

    /**
     * Export all products to CSV
     */
    public function export()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="daftar_produk_sumber_berkah_' . date('Ymd_His') . '.csv"',
        ];

        $columns = [
            'SKU', 'Barcode', 'Nama Produk', 'Kategori', 'Brand',
            'Satuan Dasar', 'Stok Saat Ini', 'Format Stok Multi-Satuan',
            'Harga Beli', 'Harga Jual Retail', 'Harga Jual Grosir',
            'Nilai Persediaan (Rp)', 'Stok Minimum', 'Lokasi Gudang', 'Status'
        ];

        $products = Product::with(['category', 'brand', 'baseUnit', 'location.warehouse', 'batches'])->get();

        $callback = function () use ($columns, $products) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($products as $p) {
                fputcsv($file, [
                    $p->sku,
                    $p->barcode,
                    $p->name,
                    $p->category?->name,
                    $p->brand?->name ?? '-',
                    $p->baseUnit?->code,
                    $p->current_stock,
                    $p->stock_formatted,
                    $p->buy_price,
                    $p->selling_price,
                    $p->wholesale_price,
                    $p->inventory_value,
                    $p->min_stock,
                    $p->location?->code ?? '-',
                    $p->is_active ? 'Aktif' : 'Nonaktif',
                ]);
            }

            fclose($file);
        };

        ActivityLog::record(
            action: 'export_products',
            module: 'Master Data',
            description: "Export data seluruh master produk ke format CSV."
        );

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Tambah / Isi stok fisik produk langsung via modal cepat (FIFO Batch & Stock Movement)
     */
    public function addStock(Request $request, Product $product)
    {
        $messages = [
            'quantity.required' => 'Jumlah kuantitas stok fisik wajib diisi.',
            'quantity.numeric' => 'Jumlah kuantitas stok fisik harus berupa angka.',
            'quantity.gt' => 'Jumlah kuantitas stok fisik harus lebih besar dari 0.',
            'warehouse_id.required' => 'Gudang penyimpanan wajib dipilih.',
            'warehouse_id.exists' => 'Gudang yang dipilih tidak ditemukan.',
        ];

        $validated = $request->validate([
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'warehouse_location_id' => ['nullable', 'exists:warehouse_locations,id'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'batch_number' => ['nullable', 'string', 'max:100'],
            'expiry_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], $messages);

        try {
            DB::transaction(function () use ($product, $validated, $request) {
                $inputQty = (float) $validated['quantity'];
                $unitId = $request->input('unit_id', $product->base_unit_id);
                $factor = 1.0;
                $unitCode = $product->baseUnit?->code ?? 'PCS';

                if ($unitId && $unitId != $product->base_unit_id) {
                    $secUnit = ProductUnit::with('unit')->where('product_id', $product->id)->where('unit_id', $unitId)->first();
                    if ($secUnit && $secUnit->conversion_factor > 0) {
                        $factor = (float) $secUnit->conversion_factor;
                        $unitCode = $secUnit->unit?->code ?? $unitCode;
                    }
                }

                $qty = $inputQty * $factor;
                
                // If user entered cost price for a packaging unit, calculate cost per base unit
                $costPrice = isset($validated['cost_price']) && $validated['cost_price'] !== ''
                    ? ($factor > 1 ? (float)$validated['cost_price'] / $factor : (float)$validated['cost_price'])
                    : (float)$product->buy_price;

                $batchNumber = !empty($validated['batch_number'])
                    ? trim($validated['batch_number'])
                    : 'BATCH-' . $product->sku . '-' . date('ymd') . '-' . Str::upper(Str::random(4));
                $locationId = !empty($validated['warehouse_location_id']) ? $validated['warehouse_location_id'] : $product->warehouse_location_id;

                $batch = InventoryBatch::create([
                    'batch_number' => $batchNumber,
                    'product_id' => $product->id,
                    'warehouse_id' => $validated['warehouse_id'],
                    'warehouse_location_id' => $locationId,
                    'initial_quantity' => $qty,
                    'available_quantity' => $qty,
                    'cost_price' => $costPrice,
                    'received_date' => now()->toDateString(),
                    'expiry_date' => $validated['expiry_date'] ?? null,
                    'status' => 'active',
                ]);

                $stockBefore = $product->current_stock;
                $stockAfter = $stockBefore + $qty;

                $noteText = $validated['notes'];
                if (empty($noteText)) {
                    $noteText = $factor > 1
                        ? "Penambahan stok kulakan {$inputQty} {$unitCode} (@ {$factor} " . ($product->baseUnit?->code ?? '') . " = {$qty} " . ($product->baseUnit?->code ?? '') . ")"
                        : "Penambahan stok fisik manual ({$qty} " . ($product->baseUnit?->code ?? '') . ")";
                }

                StockMovement::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $validated['warehouse_id'],
                    'inventory_batch_id' => $batch->id,
                    'type' => 'initial_stock',
                    'reference_number' => 'STK-' . date('ymdHis'),
                    'quantity' => $qty,
                    'quantity_before' => $stockBefore,
                    'quantity_after' => $stockAfter,
                    'unit_cost' => $costPrice,
                    'total_cost' => $qty * $costPrice,
                    'user_id' => auth()->id() ?? User::first()?->id ?? 1,
                    'notes' => $noteText,
                ]);

                ActivityLog::record(
                    action: 'add_stock',
                    module: 'Master Data',
                    description: "Penambahan stok fisik untuk {$product->name}: +{$qty} " . ($product->baseUnit?->code ?? '') . " (Batch: {$batchNumber})",
                    properties: [
                        'product_id' => $product->id,
                        'batch_id' => $batch->id,
                        'input_quantity' => $inputQty,
                        'unit' => $unitCode,
                        'base_quantity' => $qty,
                        'warehouse_id' => $validated['warehouse_id'],
                    ]
                );
            });

            return back()->with('success', "Stok fisik produk {$product->name} berhasil ditambahkan!");
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal menambahkan stok fisik: " . $e->getMessage());
        }
    }
}

