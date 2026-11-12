<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\CashTransaction;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::where('email', 'superadmin@sumberberkah.com')->first();
        $admin = User::where('email', 'admin@sumberberkah.com')->first();
        $kasir = User::where('email', 'kasir@sumberberkah.com')->first();

        // 1. Settings (Toko Bahan Kue Sumber Berkah - Peterongan, Jombang)
        $settings = [
            ['key' => 'store_name', 'value' => 'TOKO SUMBER BERKAH', 'type' => 'string', 'group' => 'general', 'description' => 'Nama Resmi Toko'],
            ['key' => 'store_tagline', 'value' => 'Pusat Bahan Kue & Perlengkapan Roti Terlengkap', 'type' => 'string', 'group' => 'general', 'description' => 'Slogan / Tagline Toko'],
            ['key' => 'store_phone', 'value' => '0812-3456-7890', 'type' => 'string', 'group' => 'general', 'description' => 'Nomor WhatsApp / Telepon Kasir & Toko'],
            ['key' => 'store_email', 'value' => 'tobaku.sumberberkah@gmail.com', 'type' => 'string', 'group' => 'general', 'description' => 'Alamat Email Toko'],
            ['key' => 'store_address', 'value' => 'Jl. Kol. H. Ismail, RT.002 RW001, Mancar Timur, Mancar, Kec. Peterongan, Kabupaten Jombang, Jawa Timur 64181', 'type' => 'text', 'group' => 'general', 'description' => 'Alamat Lengkap Toko'],
            ['key' => 'receipt_footer', 'value' => "Terima kasih atas kunjungan Anda di TOKO SUMBER BERKAH!\nPusat Bahan Kue & Perlengkapan Roti Peterongan - Jombang\nBarang yang sudah dibeli tidak dapat ditukar kecuali ada perjanjian.", 'type' => 'text', 'group' => 'pos', 'description' => 'Catatan Kaki Struk Kasir'],
            ['key' => 'receipt_footer_text', 'value' => 'Terima kasih atas kunjungan Anda di TOKO SUMBER BERKAH! Pusat Bahan Kue & Perlengkapan Roti Peterongan - Jombang', 'type' => 'text', 'group' => 'pos', 'description' => 'Catatan Kaki Struk Kasir Teks Singkat'],
            ['key' => 'tax_percentage', 'value' => '0', 'type' => 'number', 'group' => 'pos', 'description' => 'Persentase Pajak Default (%)'],
            ['key' => 'receipt_printer_width', 'value' => '58mm', 'type' => 'string', 'group' => 'pos', 'description' => 'Lebar Struk Thermal (58mm / 80mm)'],
            ['key' => 'default_customer_category', 'value' => 'umum', 'type' => 'string', 'group' => 'pos', 'description' => 'Kategori Pelanggan Default'],
            ['key' => 'low_stock_threshold', 'value' => '10', 'type' => 'number', 'group' => 'inventory', 'description' => 'Ambang Batas Stok Menipis'],
            ['key' => 'fifo_strict_mode', 'value' => '1', 'type' => 'boolean', 'group' => 'inventory', 'description' => 'Wajibkan FIFO Otomatis'],
            ['key' => 'prevent_negative_stock', 'value' => '1', 'type' => 'boolean', 'group' => 'inventory', 'description' => 'Tolak Penjualan Jika Stok Habis'],
            ['key' => 'expiry_warning_days', 'value' => '30', 'type' => 'number', 'group' => 'inventory', 'description' => 'Peringatan Kedaluwarsa (Hari Sebelum)'],
        ];

        foreach ($settings as $s) {
            Setting::updateOrCreate(['key' => $s['key']], $s);
        }

        // 2. Units (Master Satuan Toko Retail, Grosir & Toko Bahan Kue)
        $unitsData = [
            // Eceran & Kemasan Dasar
            ['code' => 'PCS', 'name' => 'Pieces / Buah (Pcs)', 'description' => 'Satuan dasar eceran untuk cetakan kue, loyang, lilin, spatula, dll'],
            ['code' => 'BKS', 'name' => 'Bungkus / Sachet (Bks)', 'description' => 'Kemasan bungkus margarin sachet, ragi, vanili, tepung kemasan kecil'],
            ['code' => 'LEMBAR', 'name' => 'Lembar / Sheet', 'description' => 'Kertas roti lembaran, mika plastik kue, label stiker toples'],
            ['code' => 'SET', 'name' => 'Set / Pasang', 'description' => 'Set cetakan spuit dekorasi, set spatula, set loyang kue'],

            // Grosir Kuantitas (Hitungan)
            ['code' => 'LUSIN', 'name' => 'Lusin (12 Pcs)', 'description' => 'Satuan grosir berisi 12 pieces / botol / sachet'],
            ['code' => 'GROSS', 'name' => 'Gross (144 Pcs / 12 Lusin)', 'description' => 'Satuan grosir besar berisi 144 pieces (12 lusin) untuk lilin, loyang, cup'],
            ['code' => 'KODI', 'name' => 'Kodi (20 Pcs / Lembar)', 'description' => 'Satuan grosir berisi 20 pieces / lembar untuk celemek, kain, plastik'],
            ['code' => 'RIM', 'name' => 'Rim (500 Lembar)', 'description' => 'Satuan kemasan 500 lembar kertas roti, baking paper, stiker'],

            // Kemasan Kardus / Wadah
            ['code' => 'PACK', 'name' => 'Pack / Mika', 'description' => 'Kemasan pack isi mika donat, kantong plastik, cup roti'],
            ['code' => 'BOX', 'name' => 'Kotak / Box', 'description' => 'Kotak kardus, kemasan hampers, boks kue tart'],
            ['code' => 'DUS', 'name' => 'Karton / Dus', 'description' => 'Karton kardus luar besar untuk margarin, cokelat compound, mentega'],
            ['code' => 'BALL', 'name' => 'Ball / Bal', 'description' => 'Bal plastik pembungkus besar atau karung kemasan produk'],
            ['code' => 'SAK', 'name' => 'Sak / Karung', 'description' => 'Karung besar untuk tepung terigu 25 kg, beras, atau gula kristal 50 kg'],
            ['code' => 'PAIL', 'name' => 'Pail / Ember', 'description' => 'Wadah pail / ember besar donat glaze 1-25 kg, margarin putih / butter'],
            ['code' => 'KALENG', 'name' => 'Kaleng / Can', 'description' => 'Susu kental manis kaleng, selai kaleng, buah kaleng (cocktail/ceri)'],
            ['code' => 'BOTOL', 'name' => 'Botol / Jar', 'description' => 'Wadah botol perisa pasta 30-100ml, sirup, rhum, essen kue'],

            // Satuan Berat
            ['code' => 'KG', 'name' => 'Kilogram (Kg)', 'description' => 'Satuan berat standar (1.000 gram) untuk tepung terigu, gula, mentega kiloan'],
            ['code' => 'GRAM', 'name' => 'Gram (Gr)', 'description' => 'Satuan berat gram untuk bahan timbang, SP, pengembang, ragi, pewarna bubuk'],
            ['code' => 'MG', 'name' => 'Miligram (Mg)', 'description' => 'Satuan berat miligram untuk takaran mikro ekstrak pekat'],

            // Satuan Volume / Cairan
            ['code' => 'LITER', 'name' => 'Liter (Lt)', 'description' => 'Satuan volume cairan (1.000 ml) untuk minyak goreng, susu cair, santan'],
            ['code' => 'ML', 'name' => 'Mililiter (Ml / CC)', 'description' => 'Satuan volume cairan mililiter untuk perisa cair, vanila, pewarna makanan'],

            // Satuan Panjang & Gulungan
            ['code' => 'ROLL', 'name' => 'Roll / Gulung', 'description' => 'Gulungan kertas roti baking paper roll, plastik wrap cling, pita hias bolu'],
            ['code' => 'METER', 'name' => 'Meter (M)', 'description' => 'Satuan panjang meter untuk pita renda kue, tali boks, selang']
        ];

        $units = [];
        foreach ($unitsData as $u) {
            $units[$u['code']] = Unit::updateOrCreate(['code' => $u['code']], $u);
        }

        // 3. Categories (Kategori Khusus Toko Bahan Kue)
        $categoriesData = [
            ['code' => 'CAT-01', 'name' => 'Tepung & Premiks', 'description' => 'Tepung terigu protein tinggi/sedang/rendah, maizena, tepung ketan, premiks bolu'],
            ['code' => 'CAT-02', 'name' => 'Margarin, Mentega & Dairy', 'description' => 'Margarin serbaguna, butter anchor, keju cheddar, whipped cream cair & bubuk'],
            ['code' => 'CAT-03', 'name' => 'Cokelat & Pasta Perisa', 'description' => 'Cokelat blok compound, cokelat bubuk, choco chips, meses, pasta pandan, pewarna makanan'],
            ['code' => 'CAT-04', 'name' => 'Gula, Pemanis & Glaze', 'description' => 'Gula pasir, gula halus donat claris, palm sugar, brown sugar, topping dip glaze donat'],
            ['code' => 'CAT-05', 'name' => 'Ragi, Pengembang & Additive', 'description' => 'Ragi instan saf-instant, fermipan, SP, Ovalet, TBM, baking powder, baking soda, vanili'],
            ['code' => 'CAT-06', 'name' => 'Kemasan, Mika & Loyang', 'description' => 'Dus brownies kraft, mika tart bolu, cup nastar, paper bag, baking paper, piping bag, spuit'],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[$c['code']] = Category::firstOrCreate(
                ['code' => $c['code']],
                ['name' => $c['name'], 'slug' => Str::slug($c['name']), 'description' => $c['description']]
            );
        }

        // 4. Brands (Merk Bahan Kue Ternama)
        $brandsData = [
            ['name' => 'Bogasari', 'description' => 'Produsen tepung terigu Segitiga Biru, Cakra Kembar, Kunci Biru'],
            ['name' => 'Blue Band', 'description' => 'Margarin serbaguna & margarin master cake aroma khas'],
            ['name' => 'Anchor', 'description' => 'Mentega murni pure New Zealand butter, whipping cream & cheese'],
            ['name' => 'Colatta', 'description' => 'Cokelat compound blok, meses cokelat & choco chips berkualitas'],
            ['name' => 'Tulip', 'description' => 'Cokelat profesional pastry & bakery untuk topping dan isian'],
            ['name' => 'Prochiz', 'description' => 'Keju cheddar parut premium olahan & keju oles spread'],
            ['name' => 'Saf-Instant', 'description' => 'Ragi instan ragi roti nomor 1 di dunia asal Perancis'],
            ['name' => 'Koepoe-Koepoe', 'description' => 'Spesialis pengemulsi kue SP, baking powder, pasta perisa & pewarna makanan'],
            ['name' => 'Elmer', 'description' => 'Dip glaze celup donat aneka rasa, selai buah & cokelat filling'],
            ['name' => 'Haan', 'description' => 'Premiks brownies kukus, wippy cream & bubuk es krim praktis'],
        ];

        $brands = [];
        foreach ($brandsData as $b) {
            $brands[$b['name']] = Brand::firstOrCreate(['name' => $b['name']], $b);
        }

        // 5. Warehouses & Locations (Gudang Toko Peterongan & Mancar Jombang)
        $wh1 = Warehouse::firstOrCreate(
            ['code' => 'GUD-PTR'],
            [
                'name' => 'Gudang Utama Toko Peterongan',
                'address' => 'Jl. Kol. H. Ismail, RT.002 RW001, Mancar Timur, Mancar, Kec. Peterongan, Jombang',
                'phone' => '0812-3456-7890',
                'description' => 'Pusat kasir POS, etalase display toko, dan ruang pendingin chiller butter/keju',
                'is_active' => true,
            ]
        );

        $wh2 = Warehouse::firstOrCreate(
            ['code' => 'GUD-MCR'],
            [
                'name' => 'Gudang Transit & Tepung Mancar',
                'address' => 'Dusun Mancar Timur, Kec. Peterongan, Kabupaten Jombang',
                'phone' => '0813-9876-5432',
                'description' => 'Gudang penyimpanan karung sak tepung terigu 25kg dan karton packaging grosir',
                'is_active' => true,
            ]
        );

        $locChiller = WarehouseLocation::firstOrCreate(
            ['code' => 'LOC-PTR-CHILLER'],
            ['warehouse_id' => $wh1->id, 'area' => 'Ruang Chiller Pendingin', 'rack' => 'CHILLER-01', 'row' => 'A-01', 'column' => '01', 'notes' => 'Suhu 2-6 C untuk Anchor Butter, Keju Prochiz, Whipping Cream']
        );

        $locCokelat = WarehouseLocation::firstOrCreate(
            ['code' => 'LOC-PTR-RAK-CKT'],
            ['warehouse_id' => $wh1->id, 'area' => 'Area Display Utama', 'rack' => 'RAK-01', 'row' => 'B-01', 'column' => '01', 'notes' => 'Display Cokelat Blok Colatta, Elmer Glaze, Pasta Koepoe']
        );

        $locKemasan = WarehouseLocation::firstOrCreate(
            ['code' => 'LOC-PTR-RAK-KMS'],
            ['warehouse_id' => $wh1->id, 'area' => 'Area Packaging', 'rack' => 'RAK-02', 'row' => 'B-02', 'column' => '02', 'notes' => 'Dus Brownies Kraft, Mika Kue Tart, Cup Nastar']
        );

        $locTepung = WarehouseLocation::firstOrCreate(
            ['code' => 'LOC-MCR-PALLET-TPG'],
            ['warehouse_id' => $wh2->id, 'area' => 'Area Pallet Sak Karung', 'rack' => 'PALLET-01', 'row' => 'A-01', 'column' => '01', 'notes' => 'Pallet Kayu Sak Tepung Terigu Segitiga Biru & Cakra Kembar 25kg']
        );

        // 6. Suppliers (Pemasok Resmi Bahan Kue di Jombang & Jawa Timur)
        $suppliersData = [
            [
                'code' => 'SUP-001',
                'name' => 'PT Bogasari Flour Mills Distributor Jombang',
                'contact_name' => 'Bpk. Bambang Sutrisno',
                'phone' => '0811-3456-789',
                'email' => 'sales.jombang@bogasari-distributor.co.id',
                'address' => 'Kawasan Pergudangan Sambong Duran No. 18',
                'city' => 'Jombang',
                'notes' => 'Distributor resmi tepung terigu Bogasari (Segitiga Biru, Cakra Kembar, Kunci Biru)',
            ],
            [
                'code' => 'SUP-002',
                'name' => 'PT Sukanda Djaya Surabaya',
                'contact_name' => 'Ibu Maya Indrayani',
                'phone' => '0812-3001-2233',
                'email' => 'maya.indrayani@sukandadjaya.com',
                'address' => 'Jl. Raya By Pass Krian - Mojokerto KM 32',
                'city' => 'Mojokerto',
                'notes' => 'Distributor produk pendingin dairy: Mentega Anchor, Keju Prochiz, Whipping Cream',
            ],
            [
                'code' => 'SUP-003',
                'name' => 'CV Prima Cokelat Nusantara',
                'contact_name' => 'Bpk. Gunawan Wibisono',
                'phone' => '0813-5566-7788',
                'email' => 'order@primacokelat.co.id',
                'address' => 'Ruko Graha Niaga Blok C-5, Jl. KH. Wahid Hasyim',
                'city' => 'Jombang',
                'notes' => 'Distributor aneka cokelat blok Colatta, Tulip, glaze Elmer, pasta dan perisa',
            ],
            [
                'code' => 'SUP-004',
                'name' => 'CV Indopack Berkah Makmur Jombang',
                'contact_name' => 'Bpk. Arif Rachman',
                'phone' => '0857-8910-1112',
                'email' => 'indopack.jbg@gmail.com',
                'address' => 'Jl. Raya Peterongan No. 45, Peterongan',
                'city' => 'Jombang',
                'notes' => 'Pabrik dan supplier aneka dus kue brownies kraft, mika tart, cupcake, dan loyang',
            ],
        ];

        $suppliers = [];
        foreach ($suppliersData as $s) {
            $suppliers[$s['code']] = Supplier::firstOrCreate(['code' => $s['code']], $s);
        }

        // 7. Customers (Pelanggan Walk-In & UMKM Bakery di Peterongan Jombang)
        $customersData = [
            [
                'code' => 'CUST-001',
                'name' => 'Pelanggan Umum Toko (Walk-In)',
                'phone' => '-',
                'email' => null,
                'address' => 'Jl. Kol. H. Ismail, Peterongan',
                'city' => 'Jombang',
                'category' => 'umum',
                'notes' => 'Customer eceran kasir langsung',
            ],
            [
                'code' => 'CUST-002',
                'name' => 'Dapur Kue Bu Anik Peterongan',
                'phone' => '0812-3112-9988',
                'email' => 'kuebuanik@gmail.com',
                'address' => 'Jl. KH. Romly Tamim No. 14, Peterongan',
                'city' => 'Jombang',
                'category' => 'pelanggan_tetap',
                'notes' => 'Home bakery spesialis bolu gulung, brownies almond, dan aneka kue kering lebaran',
            ],
            [
                'code' => 'CUST-003',
                'name' => 'Roti & Pastry Berkah Jaya Jombang',
                'phone' => '0813-3040-5060',
                'email' => 'berkahjayabakery@yahoo.com',
                'address' => 'Jl. Gus Dur No. 78, Candimulyo',
                'city' => 'Jombang',
                'category' => 'grosir',
                'notes' => 'Pabrik roti manis dan pastry, belanja karungan tepung sak dan karton margarin tempo 14 hari',
            ],
            [
                'code' => 'CUST-004',
                'name' => 'Katering & Snack Box Sejahtera Mancar',
                'phone' => '0856-4800-1234',
                'email' => 'sejahtera.mancar@gmail.com',
                'address' => 'Dusun Mancar RT 03 RW 02, Peterongan',
                'city' => 'Jombang',
                'category' => 'reseller',
                'notes' => 'Spesialis snack box hajatan instansi dan hajatan warga Peterongan',
            ],
        ];

        $customers = [];
        foreach ($customersData as $c) {
            $customers[$c['code']] = Customer::firstOrCreate(['code' => $c['code']], $c);
        }

        // 8. Products with Multi-Units and Real FIFO Batches (Produk Bahan Kue)

        // PRODUK 1: Tepung Terigu Segitiga Biru 1 kg (Base: KG. 1 SAK = 25 KG)
        $segt = Product::firstOrCreate(
            ['sku' => 'TPG-SGT-01KG'],
            [
                'barcode' => '8992775010015',
                'name' => 'Tepung Terigu Segitiga Biru 1 kg',
                'slug' => 'tepung-terigu-segitiga-biru-1-kg',
                'image' => 'products/segitiga-biru-1kg.png',
                'category_id' => $categories['CAT-01']->id,
                'brand_id' => $brands['Bogasari']->id,
                'description' => 'Tepung terigu protein sedang serbaguna untuk aneka cake, martabak manis, bolu, dan gorengan',
                'base_unit_id' => $units['KG']->id,
                'purchase_unit_id' => $units['SAK']->id,
                'selling_unit_id' => $units['KG']->id,
                'buy_price' => 10500,     // Rp 10.500 / KG
                'selling_price' => 12500, // Rp 12.500 / KG retail
                'wholesale_price' => 11500, // Rp 11.500 / KG grosir
                'min_stock' => 50,
                'weight' => 1000,
                'warehouse_location_id' => $locTepung->id,
                'has_expiry' => true,
                'is_active' => true,
            ]
        );

        ProductUnit::firstOrCreate(
            ['product_id' => $segt->id, 'unit_id' => $units['SAK']->id],
            [
                'conversion_factor' => 25, // 1 SAK = 25 KG
                'buy_price' => 255000,
                'selling_price' => 295000,
                'wholesale_price' => 275000,
                'is_base' => false,
            ]
        );

        if (!InventoryBatch::where('product_id', $segt->id)->exists()) {
            // Batch 1 (Older batch - FIFO test): 500 KG @ Rp 10.200 (10 hari lalu)
            $b1Segt = InventoryBatch::create([
                'batch_number' => 'BATCH-SGT-2610-01',
                'product_id' => $segt->id,
                'warehouse_id' => $wh2->id,
                'warehouse_location_id' => $locTepung->id,
                'initial_quantity' => 500, // 20 SAK
                'available_quantity' => 500,
                'cost_price' => 10200,
                'received_date' => now()->subDays(12)->toDateString(),
                'expiry_date' => now()->addMonths(10)->toDateString(),
                'status' => 'active',
            ]);

            StockMovement::create([
                'product_id' => $segt->id,
                'warehouse_id' => $wh2->id,
                'inventory_batch_id' => $b1Segt->id,
                'type' => 'initial_stock',
                'reference_number' => 'INIT-SGT-001',
                'quantity' => 500,
                'quantity_before' => 0,
                'quantity_after' => 500,
                'unit_cost' => 10200,
                'total_cost' => 500 * 10200,
                'user_id' => $superadmin->id,
                'notes' => 'Stok awal 20 Sak @ 25 KG (Batch A)',
            ]);

            // Batch 2 (Newer batch - FIFO): 500 KG @ Rp 10.500 (3 hari lalu)
            $b2Segt = InventoryBatch::create([
                'batch_number' => 'BATCH-SGT-2610-02',
                'product_id' => $segt->id,
                'warehouse_id' => $wh2->id,
                'warehouse_location_id' => $locTepung->id,
                'initial_quantity' => 500,
                'available_quantity' => 500,
                'cost_price' => 10500,
                'received_date' => now()->subDays(3)->toDateString(),
                'expiry_date' => now()->addMonths(12)->toDateString(),
                'status' => 'active',
            ]);

            StockMovement::create([
                'product_id' => $segt->id,
                'warehouse_id' => $wh2->id,
                'inventory_batch_id' => $b2Segt->id,
                'type' => 'initial_stock',
                'reference_number' => 'INIT-SGT-002',
                'quantity' => 500,
                'quantity_before' => 500,
                'quantity_after' => 1000,
                'unit_cost' => 10500,
                'total_cost' => 500 * 10500,
                'user_id' => $superadmin->id,
                'notes' => 'Restock 20 Sak @ 25 KG (Batch B)',
            ]);
        }

        // PRODUK 2: Tepung Terigu Cakra Kembar 1 kg (Base: KG. 1 SAK = 25 KG)
        $ckr = Product::firstOrCreate(
            ['sku' => 'TPG-CKR-01KG'],
            [
                'barcode' => '8992775010022',
                'name' => 'Tepung Terigu Cakra Kembar Premium 1 kg',
                'slug' => 'tepung-terigu-cakra-kembar-premium-1-kg',
                'image' => 'products/cakra-kembar-1kg.png',
                'category_id' => $categories['CAT-01']->id,
                'brand_id' => $brands['Bogasari']->id,
                'description' => 'Tepung terigu protein tinggi bermutu untuk pembuatan roti manis, roti tawar, donat, dan mie kenyal',
                'base_unit_id' => $units['KG']->id,
                'purchase_unit_id' => $units['SAK']->id,
                'selling_unit_id' => $units['KG']->id,
                'buy_price' => 11500,     // Rp 11.500 / KG
                'selling_price' => 13500, // Rp 13.500 / KG retail
                'wholesale_price' => 12500,
                'min_stock' => 50,
                'weight' => 1000,
                'warehouse_location_id' => $locTepung->id,
                'has_expiry' => true,
                'is_active' => true,
            ]
        );

        ProductUnit::firstOrCreate(
            ['product_id' => $ckr->id, 'unit_id' => $units['SAK']->id],
            [
                'conversion_factor' => 25,
                'buy_price' => 280000,
                'selling_price' => 320000,
                'wholesale_price' => 300000,
                'is_base' => false,
            ]
        );

        if (!InventoryBatch::where('product_id', $ckr->id)->exists()) {
            $bCkr = InventoryBatch::create([
                'batch_number' => 'BATCH-CKR-2610-01',
                'product_id' => $ckr->id,
                'warehouse_id' => $wh2->id,
                'warehouse_location_id' => $locTepung->id,
                'initial_quantity' => 750, // 30 Sak @ 25 KG
                'available_quantity' => 750,
                'cost_price' => 11500,
                'received_date' => now()->subDays(8)->toDateString(),
                'expiry_date' => now()->addMonths(11)->toDateString(),
                'status' => 'active',
            ]);

            StockMovement::create([
                'product_id' => $ckr->id,
                'warehouse_id' => $wh2->id,
                'inventory_batch_id' => $bCkr->id,
                'type' => 'initial_stock',
                'reference_number' => 'INIT-CKR-001',
                'quantity' => 750,
                'quantity_before' => 0,
                'quantity_after' => 750,
                'unit_cost' => 11500,
                'total_cost' => 750 * 11500,
                'user_id' => $superadmin->id,
                'notes' => 'Stok awal 30 Sak Cakra Kembar (750 KG)',
            ]);
        }

        // PRODUK 3: Margarin Blue Band Master Cake 1 kg (Base: BKS. 1 DUS = 15 BKS)
        $bb = Product::firstOrCreate(
            ['sku' => 'MGR-BLB-01KG'],
            [
                'barcode' => '8999999054128',
                'name' => 'Margarin Blue Band Master Cake Margarine 1 kg',
                'slug' => 'margarin-blue-band-master-cake-1-kg',
                'image' => 'products/blue-band-master-1kg.png',
                'category_id' => $categories['CAT-02']->id,
                'brand_id' => $brands['Blue Band']->id,
                'description' => 'Margarin khusus pembuatan bolu, lapis legit, dan cookies dengan aroma butter khas wangi menggugah selera',
                'base_unit_id' => $units['BKS']->id,
                'purchase_unit_id' => $units['DUS']->id,
                'selling_unit_id' => $units['BKS']->id,
                'buy_price' => 38000,     // Rp 38.000 / BKS
                'selling_price' => 44000, // Rp 44.000 / BKS
                'wholesale_price' => 41000,
                'min_stock' => 15,
                'weight' => 1000,
                'warehouse_location_id' => $locCokelat->id,
                'has_expiry' => true,
                'is_active' => true,
            ]
        );

        ProductUnit::firstOrCreate(
            ['product_id' => $bb->id, 'unit_id' => $units['DUS']->id],
            [
                'conversion_factor' => 15, // 1 Dus = 15 bungkus
                'buy_price' => 555000,
                'selling_price' => 630000,
                'wholesale_price' => 595000,
                'is_base' => false,
            ]
        );

        if (!InventoryBatch::where('product_id', $bb->id)->exists()) {
            $bBb = InventoryBatch::create([
                'batch_number' => 'BATCH-BLB-2610-01',
                'product_id' => $bb->id,
                'warehouse_id' => $wh1->id,
                'warehouse_location_id' => $locCokelat->id,
                'initial_quantity' => 150, // 10 Dus @ 15 BKS
                'available_quantity' => 150,
                'cost_price' => 38000,
                'received_date' => now()->subDays(6)->toDateString(),
                'expiry_date' => now()->addMonths(16)->toDateString(),
                'status' => 'active',
            ]);

            StockMovement::create([
                'product_id' => $bb->id,
                'warehouse_id' => $wh1->id,
                'inventory_batch_id' => $bBb->id,
                'type' => 'initial_stock',
                'reference_number' => 'INIT-BLB-001',
                'quantity' => 150,
                'quantity_before' => 0,
                'quantity_after' => 150,
                'unit_cost' => 38000,
                'total_cost' => 150 * 38000,
                'user_id' => $superadmin->id,
                'notes' => 'Stok awal 10 Dus Blue Band Master (150 BKS)',
            ]);
        }

        // PRODUK 4: Mentega Anchor Unsalted Pure New Zealand Butter 227 gr (Base: PCS, Dus: 20 PCS)
        $anc = Product::firstOrCreate(
            ['sku' => 'BTR-ANC-227G'],
            [
                'barcode' => '9414100123456',
                'name' => 'Mentega Anchor Unsalted Pure New Zealand Butter 227 gr',
                'slug' => 'mentega-anchor-unsalted-pure-butter-227-gr',
                'image' => 'products/anchor-butter-227g.png',
                'category_id' => $categories['CAT-02']->id,
                'brand_id' => $brands['Anchor']->id,
                'description' => 'Mentega butter murni tanpa garam dari susu sapi New Zealand untuk nastar premium, croissant, dan pastry lembut',
                'base_unit_id' => $units['PCS']->id,
                'purchase_unit_id' => $units['DUS']->id,
                'selling_unit_id' => $units['PCS']->id,
                'buy_price' => 45000,
                'selling_price' => 53000,
                'wholesale_price' => 49000,
                'min_stock' => 10,
                'weight' => 227,
                'warehouse_location_id' => $locChiller->id,
                'has_expiry' => true,
                'is_active' => true,
            ]
        );

        ProductUnit::firstOrCreate(
            ['product_id' => $anc->id, 'unit_id' => $units['DUS']->id],
            [
                'conversion_factor' => 20, // 1 Dus = 20 Pcs
                'buy_price' => 880000,
                'selling_price' => 1020000,
                'wholesale_price' => 950000,
                'is_base' => false,
            ]
        );

        if (!InventoryBatch::where('product_id', $anc->id)->exists()) {
            $bAnc = InventoryBatch::create([
                'batch_number' => 'BATCH-ANC-2610-01',
                'product_id' => $anc->id,
                'warehouse_id' => $wh1->id,
                'warehouse_location_id' => $locChiller->id,
                'initial_quantity' => 60, // 3 Dus @ 20 PCS
                'available_quantity' => 60,
                'cost_price' => 45000,
                'received_date' => now()->subDays(5)->toDateString(),
                'expiry_date' => now()->addMonths(8)->toDateString(),
                'status' => 'active',
            ]);

            StockMovement::create([
                'product_id' => $anc->id,
                'warehouse_id' => $wh1->id,
                'inventory_batch_id' => $bAnc->id,
                'type' => 'initial_stock',
                'reference_number' => 'INIT-ANC-001',
                'quantity' => 60,
                'quantity_before' => 0,
                'quantity_after' => 60,
                'unit_cost' => 45000,
                'total_cost' => 60 * 45000,
                'user_id' => $superadmin->id,
                'notes' => 'Stok awal Chiller 3 Dus Anchor Butter (60 Pcs)',
            ]);
        }

        // PRODUK 5: Cokelat Blok Colatta Dark Compound 1 kg (Base: PCS, Dus: 12 PCS)
        $clt = Product::firstOrCreate(
            ['sku' => 'CKT-CLT-DRK1K'],
            [
                'barcode' => '8993175110123',
                'name' => 'Cokelat Blok Colatta Dark Compound Chocolate 1 kg',
                'slug' => 'cokelat-blok-colatta-dark-compound-1-kg',
                'image' => 'products/colatta-dark-1kg.png',
                'category_id' => $categories['CAT-03']->id,
                'brand_id' => $brands['Colatta']->id,
                'description' => 'Cokelat compound blok hitam pekat mudah dilelehkan, hasil mengkilap sempurna untuk ganache dan brownies',
                'base_unit_id' => $units['PCS']->id,
                'purchase_unit_id' => $units['DUS']->id,
                'selling_unit_id' => $units['PCS']->id,
                'buy_price' => 49000,
                'selling_price' => 58000,
                'wholesale_price' => 54000,
                'min_stock' => 12,
                'weight' => 1000,
                'warehouse_location_id' => $locCokelat->id,
                'has_expiry' => true,
                'is_active' => true,
            ]
        );

        ProductUnit::firstOrCreate(
            ['product_id' => $clt->id, 'unit_id' => $units['DUS']->id],
            [
                'conversion_factor' => 12, // 1 Dus = 12 Balok
                'buy_price' => 576000,
                'selling_price' => 672000,
                'wholesale_price' => 625000,
                'is_base' => false,
            ]
        );

        if (!InventoryBatch::where('product_id', $clt->id)->exists()) {
            $bClt = InventoryBatch::create([
                'batch_number' => 'BATCH-CLT-2610-01',
                'product_id' => $clt->id,
                'warehouse_id' => $wh1->id,
                'warehouse_location_id' => $locCokelat->id,
                'initial_quantity' => 120, // 10 Dus @ 12 PCS
                'available_quantity' => 120,
                'cost_price' => 49000,
                'received_date' => now()->subDays(7)->toDateString(),
                'expiry_date' => now()->addMonths(15)->toDateString(),
                'status' => 'active',
            ]);

            StockMovement::create([
                'product_id' => $clt->id,
                'warehouse_id' => $wh1->id,
                'inventory_batch_id' => $bClt->id,
                'type' => 'initial_stock',
                'reference_number' => 'INIT-CLT-001',
                'quantity' => 120,
                'quantity_before' => 0,
                'quantity_after' => 120,
                'unit_cost' => 49000,
                'total_cost' => 120 * 49000,
                'user_id' => $superadmin->id,
                'notes' => 'Stok awal 10 Dus Colatta Dark (120 Balok)',
            ]);
        }

        // PRODUK 6: Keju Cheddar Prochiz Gold 2 kg Block (Base: PCS, Dus: 8 PCS)
        $prc = Product::firstOrCreate(
            ['sku' => 'KJU-PRC-GLD2K'],
            [
                'barcode' => '8997011980011',
                'name' => 'Keju Olahan Cheddar Prochiz Gold Block 2 kg',
                'slug' => 'keju-cheddar-prochiz-gold-block-2-kg',
                'image' => 'products/prochiz-gold-2kg.png',
                'category_id' => $categories['CAT-02']->id,
                'brand_id' => $brands['Prochiz']->id,
                'description' => 'Keju cheddar block ekonomis kualitas premium, mudah diparut panjang tidak mudah patah, tahan panas panggang',
                'base_unit_id' => $units['PCS']->id,
                'purchase_unit_id' => $units['DUS']->id,
                'selling_unit_id' => $units['PCS']->id,
                'buy_price' => 128000,
                'selling_price' => 145000,
                'wholesale_price' => 137000,
                'min_stock' => 8,
                'weight' => 2000,
                'warehouse_location_id' => $locChiller->id,
                'has_expiry' => true,
                'is_active' => true,
            ]
        );

        ProductUnit::firstOrCreate(
            ['product_id' => $prc->id, 'unit_id' => $units['DUS']->id],
            [
                'conversion_factor' => 8, // 1 Dus = 8 Balok @ 2 kg
                'buy_price' => 1000000,
                'selling_price' => 1120000,
                'wholesale_price' => 1060000,
                'is_base' => false,
            ]
        );

        if (!InventoryBatch::where('product_id', $prc->id)->exists()) {
            $bPrc = InventoryBatch::create([
                'batch_number' => 'BATCH-PRC-2610-01',
                'product_id' => $prc->id,
                'warehouse_id' => $wh1->id,
                'warehouse_location_id' => $locChiller->id,
                'initial_quantity' => 40, // 5 Dus @ 8 PCS
                'available_quantity' => 40,
                'cost_price' => 128000,
                'received_date' => now()->subDays(6)->toDateString(),
                'expiry_date' => now()->addMonths(9)->toDateString(),
                'status' => 'active',
            ]);

            StockMovement::create([
                'product_id' => $prc->id,
                'warehouse_id' => $wh1->id,
                'inventory_batch_id' => $bPrc->id,
                'type' => 'initial_stock',
                'reference_number' => 'INIT-PRC-001',
                'quantity' => 40,
                'quantity_before' => 0,
                'quantity_after' => 40,
                'unit_cost' => 128000,
                'total_cost' => 40 * 128000,
                'user_id' => $superadmin->id,
                'notes' => 'Stok awal Chiller 5 Dus Prochiz Gold (40 Pcs)',
            ]);
        }

        // PRODUK 7: Ragi Instan Saf-Instant Gold 500 gr (Base: BKS, Dus: 20 BKS)
        $saf = Product::firstOrCreate(
            ['sku' => 'RGI-SAF-GLD50'],
            [
                'barcode' => '3188890001018',
                'name' => 'Ragi Instan Saf-Instant Gold 500 gr',
                'slug' => 'ragi-instan-saf-instant-gold-500-gr',
                'image' => 'products/saf-instant-500g.png',
                'category_id' => $categories['CAT-05']->id,
                'brand_id' => $brands['Saf-Instant']->id,
                'description' => 'Ragi instan kualitas dunia khusus adonan manis dengan kadar gula diatas 8%, fermentasi cepat dan stabil',
                'base_unit_id' => $units['BKS']->id,
                'purchase_unit_id' => $units['DUS']->id,
                'selling_unit_id' => $units['BKS']->id,
                'buy_price' => 41000,
                'selling_price' => 48000,
                'wholesale_price' => 44500,
                'min_stock' => 10,
                'weight' => 500,
                'warehouse_location_id' => $locCokelat->id,
                'has_expiry' => true,
                'is_active' => true,
            ]
        );

        ProductUnit::firstOrCreate(
            ['product_id' => $saf->id, 'unit_id' => $units['DUS']->id],
            [
                'conversion_factor' => 20, // 1 Dus = 20 Bungkus
                'buy_price' => 800000,
                'selling_price' => 920000,
                'wholesale_price' => 860000,
                'is_base' => false,
            ]
        );

        if (!InventoryBatch::where('product_id', $saf->id)->exists()) {
            $bSaf = InventoryBatch::create([
                'batch_number' => 'BATCH-SAF-2610-01',
                'product_id' => $saf->id,
                'warehouse_id' => $wh1->id,
                'warehouse_location_id' => $locCokelat->id,
                'initial_quantity' => 80, // 4 Dus @ 20 BKS
                'available_quantity' => 80,
                'cost_price' => 41000,
                'received_date' => now()->subDays(10)->toDateString(),
                'expiry_date' => now()->addMonths(20)->toDateString(),
                'status' => 'active',
            ]);

            StockMovement::create([
                'product_id' => $saf->id,
                'warehouse_id' => $wh1->id,
                'inventory_batch_id' => $bSaf->id,
                'type' => 'initial_stock',
                'reference_number' => 'INIT-SAF-001',
                'quantity' => 80,
                'quantity_before' => 0,
                'quantity_after' => 80,
                'unit_cost' => 41000,
                'total_cost' => 80 * 41000,
                'user_id' => $superadmin->id,
                'notes' => 'Stok awal 4 Dus Saf-Instant Gold (80 Bungkus)',
            ]);
        }

        // PRODUK 8: Pengemulsi Kue SP Koepoe-Koepoe 70 gr (Base: BOTOL, Lusin: 12 BOTOL)
        $sp = Product::firstOrCreate(
            ['sku' => 'EML-KPE-SP70G'],
            [
                'barcode' => '8992741910011',
                'name' => 'Pengemulsi Kue SP Koepoe-Koepoe 70 gr',
                'slug' => 'pengemulsi-kue-sp-koepoe-koepoe-70-gr',
                'image' => 'products/sp-koepoe-70g.png',
                'category_id' => $categories['CAT-05']->id,
                'brand_id' => $brands['Koepoe-Koepoe']->id,
                'description' => 'Bahan pengemulsi cake emulsifier untuk melembutkan tekstur bolu, menstabilkan adonan telur agar mengembang sempurna',
                'base_unit_id' => $units['BOTOL']->id,
                'purchase_unit_id' => $units['LUSIN']->id,
                'selling_unit_id' => $units['BOTOL']->id,
                'buy_price' => 10500,
                'selling_price' => 13500,
                'wholesale_price' => 12000,
                'min_stock' => 24,
                'weight' => 70,
                'warehouse_location_id' => $locCokelat->id,
                'has_expiry' => true,
                'is_active' => true,
            ]
        );

        ProductUnit::firstOrCreate(
            ['product_id' => $sp->id, 'unit_id' => $units['LUSIN']->id],
            [
                'conversion_factor' => 12, // 1 Lusin = 12 Botol
                'buy_price' => 120000,
                'selling_price' => 150000,
                'wholesale_price' => 138000,
                'is_base' => false,
            ]
        );

        if (!InventoryBatch::where('product_id', $sp->id)->exists()) {
            $bSp = InventoryBatch::create([
                'batch_number' => 'BATCH-SP-2610-01',
                'product_id' => $sp->id,
                'warehouse_id' => $wh1->id,
                'warehouse_location_id' => $locCokelat->id,
                'initial_quantity' => 120, // 10 Lusin @ 12 Botol
                'available_quantity' => 120,
                'cost_price' => 10500,
                'received_date' => now()->subDays(9)->toDateString(),
                'expiry_date' => now()->addMonths(18)->toDateString(),
                'status' => 'active',
            ]);

            StockMovement::create([
                'product_id' => $sp->id,
                'warehouse_id' => $wh1->id,
                'inventory_batch_id' => $bSp->id,
                'type' => 'initial_stock',
                'reference_number' => 'INIT-SP-001',
                'quantity' => 120,
                'quantity_before' => 0,
                'quantity_after' => 120,
                'unit_cost' => 10500,
                'total_cost' => 120 * 10500,
                'user_id' => $superadmin->id,
                'notes' => 'Stok awal 10 Lusin SP Koepoe (120 Botol)',
            ]);
        }

        // PRODUK 9: Elmer Dip Glaze Donat Dark Chocolate 1 kg (Base: BOTOL/PAIL, Dus: 6 BOTOL)
        $glz = Product::firstOrCreate(
            ['sku' => 'GLZ-ELM-DRK1K'],
            [
                'barcode' => '8994567120034',
                'name' => 'Elmer Dip Glaze Donat Dark Chocolate 1 kg Pail',
                'slug' => 'elmer-dip-glaze-donat-dark-chocolate-1-kg',
                'image' => 'products/elmer-glaze-1kg.png',
                'category_id' => $categories['CAT-04']->id,
                'brand_id' => $brands['Elmer']->id,
                'description' => 'Cokelat celup glaze donat siap pakai, tekstur lumer berkilau basah, tidak retak saat dingin, cocok untuk donat & pisang krispi',
                'base_unit_id' => $units['BOTOL']->id,
                'purchase_unit_id' => $units['DUS']->id,
                'selling_unit_id' => $units['BOTOL']->id,
                'buy_price' => 46000,
                'selling_price' => 55000,
                'wholesale_price' => 50000,
                'min_stock' => 12,
                'weight' => 1000,
                'warehouse_location_id' => $locCokelat->id,
                'has_expiry' => true,
                'is_active' => true,
            ]
        );

        ProductUnit::firstOrCreate(
            ['product_id' => $glz->id, 'unit_id' => $units['DUS']->id],
            [
                'conversion_factor' => 6, // 1 Dus = 6 Pail
                'buy_price' => 270000,
                'selling_price' => 315000,
                'wholesale_price' => 290000,
                'is_base' => false,
            ]
        );

        if (!InventoryBatch::where('product_id', $glz->id)->exists()) {
            $bGlz = InventoryBatch::create([
                'batch_number' => 'BATCH-GLZ-2610-01',
                'product_id' => $glz->id,
                'warehouse_id' => $wh1->id,
                'warehouse_location_id' => $locCokelat->id,
                'initial_quantity' => 60, // 10 Dus @ 6 Pail
                'available_quantity' => 60,
                'cost_price' => 46000,
                'received_date' => now()->subDays(7)->toDateString(),
                'expiry_date' => now()->addMonths(12)->toDateString(),
                'status' => 'active',
            ]);

            StockMovement::create([
                'product_id' => $glz->id,
                'warehouse_id' => $wh1->id,
                'inventory_batch_id' => $bGlz->id,
                'type' => 'initial_stock',
                'reference_number' => 'INIT-GLZ-001',
                'quantity' => 60,
                'quantity_before' => 0,
                'quantity_after' => 60,
                'unit_cost' => 46000,
                'total_cost' => 60 * 46000,
                'user_id' => $superadmin->id,
                'notes' => 'Stok awal 10 Dus Elmer Glaze Donat (60 Pail)',
            ]);
        }

        // PRODUK 10: Dus Brownies Kraft Tebal 20x10x5 cm Window Mika (Base: PCS, Pack: 50 PCS)
        $dus = Product::firstOrCreate(
            ['sku' => 'KMS-BRW-20X10'],
            [
                'barcode' => '8998888201005',
                'name' => 'Dus Brownies Kraft Tebal 20x10x5 cm Window Mika',
                'slug' => 'dus-brownies-kraft-tebal-20x10x5-cm-window-mika',
                'image' => 'products/dus-brownies-20x10.png',
                'category_id' => $categories['CAT-06']->id,
                'brand_id' => null,
                'description' => 'Kotak kemasan kue brownies bahan kraft cokelat tebal ramah lingkungan dengan jendela mika transparan elegan',
                'base_unit_id' => $units['PCS']->id,
                'purchase_unit_id' => $units['PACK']->id,
                'selling_unit_id' => $units['PCS']->id,
                'buy_price' => 1400,     // Rp 1.400 / PCS
                'selling_price' => 2000, // Rp 2.000 / PCS
                'wholesale_price' => 1700,
                'min_stock' => 100,
                'weight' => 35,
                'warehouse_location_id' => $locKemasan->id,
                'has_expiry' => false,
                'is_active' => true,
            ]
        );

        ProductUnit::firstOrCreate(
            ['product_id' => $dus->id, 'unit_id' => $units['PACK']->id],
            [
                'conversion_factor' => 50, // 1 PACK = 50 PCS
                'buy_price' => 65000,
                'selling_price' => 90000,
                'wholesale_price' => 78000,
                'is_base' => false,
            ]
        );

        if (!InventoryBatch::where('product_id', $dus->id)->exists()) {
            $bDus = InventoryBatch::create([
                'batch_number' => 'BATCH-BRW-2610-01',
                'product_id' => $dus->id,
                'warehouse_id' => $wh1->id,
                'warehouse_location_id' => $locKemasan->id,
                'initial_quantity' => 1000, // 20 PACK @ 50 PCS
                'available_quantity' => 1000,
                'cost_price' => 1400,
                'received_date' => now()->subDays(11)->toDateString(),
                'status' => 'active',
            ]);

            StockMovement::create([
                'product_id' => $dus->id,
                'warehouse_id' => $wh1->id,
                'inventory_batch_id' => $bDus->id,
                'type' => 'initial_stock',
                'reference_number' => 'INIT-BRW-001',
                'quantity' => 1000,
                'quantity_before' => 0,
                'quantity_after' => 1000,
                'unit_cost' => 1400,
                'total_cost' => 1000 * 1400,
                'user_id' => $superadmin->id,
                'notes' => 'Stok awal 20 Pack Dus Brownies (1.000 PCS)',
            ]);
        }

        // 9. Initial Financial Setup: Modal Kas Awal Toko Bahan Kue
        CashTransaction::firstOrCreate(
            ['transaction_number' => 'KAS-IN-INIT-001'],
            [
                'type' => 'cash_in',
                'category' => 'Modal Awal Kas Toko',
                'amount' => 5000000,
                'transaction_date' => now()->subDays(14)->toDateString(),
                'payment_method' => 'cash',
                'description' => 'Modal awal kas kasir dan uang kembalian TOKO SUMBER BERKAH Peterongan Jombang',
                'user_id' => $superadmin->id,
            ]
        );

        // Operasional Toko Bahan Kue (Listrik Chiller, Roll Kasir, Internet)
        $expensesData = [
            ['category' => 'Listrik & Chiller Toko', 'amount' => 550000, 'notes' => 'Tagihan listrik PLN toko & operasional chiller pendingin butter/keju', 'days_ago' => 5],
            ['category' => 'Internet & Telepon', 'amount' => 300000, 'notes' => 'Langganan WiFi internet kasir POS Indihome Peterongan', 'days_ago' => 4],
            ['category' => 'ATK & Perlengkapan Toko', 'amount' => 140000, 'notes' => 'Kertas struk kasir thermal 58mm (10 roll) dan lakban segel', 'days_ago' => 2],
        ];

        foreach ($expensesData as $idx => $exp) {
            $expNum = 'EXP-' . now()->format('Ymd') . '-000' . ($idx + 1);
            if (!Expense::where('expense_number', $expNum)->exists()) {
                $cashOut = CashTransaction::create([
                    'transaction_number' => 'KAS-OUT-EXP-' . ($idx + 1),
                    'type' => 'cash_out',
                    'category' => 'Beban: ' . $exp['category'],
                    'amount' => $exp['amount'],
                    'transaction_date' => now()->subDays($exp['days_ago'])->toDateString(),
                    'payment_method' => 'cash',
                    'description' => $exp['notes'],
                    'user_id' => $admin->id,
                ]);

                Expense::create([
                    'expense_number' => $expNum,
                    'category' => $exp['category'],
                    'amount' => $exp['amount'],
                    'expense_date' => now()->subDays($exp['days_ago'])->toDateString(),
                    'payment_method' => 'cash',
                    'cash_transaction_id' => $cashOut->id,
                    'notes' => $exp['notes'],
                    'user_id' => $admin->id,
                ]);
            }
        }

        // 10. Sample Realistic Sales Transactions (Transaksi Penjualan Riil Tobaku)
        // Penjualan 1: Eceran Kasir Walk-In (Segitiga Biru 2 KG + Blue Band 1 BKS + SP Koepoe 1 BOTOL)
        $inv1 = 'INV-' . now()->subDays(2)->format('Ymd') . '-0001';
        if (!Sale::where('invoice_number', $inv1)->exists()) {
            $custWalkin = $customers['CUST-001'];
            
            // Deduct FIFO batches
            $batchSegt = InventoryBatch::where('product_id', $segt->id)->where('available_quantity', '>=', 2)->orderBy('received_date')->first();
            $batchBb = InventoryBatch::where('product_id', $bb->id)->where('available_quantity', '>=', 1)->orderBy('received_date')->first();
            $batchSp = InventoryBatch::where('product_id', $sp->id)->where('available_quantity', '>=', 1)->orderBy('received_date')->first();

            $cogsItem1 = 2 * ($batchSegt ? $batchSegt->cost_price : 10200);
            $cogsItem2 = 1 * ($batchBb ? $batchBb->cost_price : 38000);
            $cogsItem3 = 1 * ($batchSp ? $batchSp->cost_price : 10500);
            $totalCogs1 = $cogsItem1 + $cogsItem2 + $cogsItem3;

            $subtotal1 = (2 * 12500) + (1 * 44000) + (1 * 13500); // 25.000 + 44.000 + 13.500 = 82.500

            $sale1 = Sale::create([
                'invoice_number' => $inv1,
                'customer_id' => $custWalkin->id,
                'user_id' => $kasir->id,
                'sale_date' => now()->subDays(2)->setHour(9)->setMinute(15),
                'price_type' => 'retail',
                'subtotal' => $subtotal1,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $subtotal1,
                'total_cogs' => $totalCogs1,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'paid_amount' => 100000,
                'change_amount' => 17500,
                'status' => 'completed',
                'notes' => 'Pembelian eceran walk-in bahan kue',
            ]);

            SaleItem::create([
                'sale_id' => $sale1->id,
                'product_id' => $segt->id,
                'unit_id' => $units['KG']->id,
                'quantity' => 2,
                'conversion_factor' => 1,
                'base_quantity' => 2,
                'unit_price' => 12500,
                'discount_amount' => 0,
                'subtotal' => 25000,
                'cogs_total' => $cogsItem1,
            ]);
            if ($batchSegt) { $batchSegt->decrement('available_quantity', 2); }

            SaleItem::create([
                'sale_id' => $sale1->id,
                'product_id' => $bb->id,
                'unit_id' => $units['BKS']->id,
                'quantity' => 1,
                'conversion_factor' => 1,
                'base_quantity' => 1,
                'unit_price' => 44000,
                'discount_amount' => 0,
                'subtotal' => 44000,
                'cogs_total' => $cogsItem2,
            ]);
            if ($batchBb) { $batchBb->decrement('available_quantity', 1); }

            SaleItem::create([
                'sale_id' => $sale1->id,
                'product_id' => $sp->id,
                'unit_id' => $units['BOTOL']->id,
                'quantity' => 1,
                'conversion_factor' => 1,
                'base_quantity' => 1,
                'unit_price' => 13500,
                'discount_amount' => 0,
                'subtotal' => 13500,
                'cogs_total' => $cogsItem3,
            ]);
            if ($batchSp) { $batchSp->decrement('available_quantity', 1); }

            CashTransaction::create([
                'transaction_number' => 'KAS-SALE-001',
                'type' => 'cash_in',
                'category' => 'Penjualan Kasir POS',
                'amount' => $subtotal1,
                'transaction_date' => now()->subDays(2)->toDateString(),
                'payment_method' => 'cash',
                'description' => "Penjualan Faktur {$inv1}",
                'user_id' => $kasir->id,
            ]);
        }

        // Penjualan 2: Dapur Kue Bu Anik Peterongan (1 Sak Segitiga Biru 25kg + 2 Pcs Colatta Dark + 1 Pack Dus Brownies 50 pcs)
        $inv2 = 'INV-' . now()->subDays(1)->format('Ymd') . '-0002';
        if (!Sale::where('invoice_number', $inv2)->exists()) {
            $custAnik = $customers['CUST-002'];
            
            $batchSegt = InventoryBatch::where('product_id', $segt->id)->where('available_quantity', '>=', 25)->orderBy('received_date')->first();
            $batchClt = InventoryBatch::where('product_id', $clt->id)->where('available_quantity', '>=', 2)->orderBy('received_date')->first();
            $batchDus = InventoryBatch::where('product_id', $dus->id)->where('available_quantity', '>=', 50)->orderBy('received_date')->first();

            $cogs1 = 25 * ($batchSegt ? $batchSegt->cost_price : 10200);
            $cogs2 = 2 * ($batchClt ? $batchClt->cost_price : 49000);
            $cogs3 = 50 * ($batchDus ? $batchDus->cost_price : 1400);
            $totalCogs2 = $cogs1 + $cogs2 + $cogs3;

            // Harga: 1 Sak Segitiga (Rp 295.000) + 2 Pcs Colatta @ 58.000 (116.000) + 1 Pack Dus Brownies 50 pcs (Rp 90.000)
            $subtotal2 = 295000 + 116000 + 90000; // 501.000

            $sale2 = Sale::create([
                'invoice_number' => $inv2,
                'customer_id' => $custAnik->id,
                'user_id' => $kasir->id,
                'sale_date' => now()->subDays(1)->setHour(14)->setMinute(30),
                'price_type' => 'retail',
                'subtotal' => $subtotal2,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $subtotal2,
                'total_cogs' => $totalCogs2,
                'payment_method' => 'transfer',
                'payment_status' => 'paid',
                'paid_amount' => $subtotal2,
                'change_amount' => 0,
                'status' => 'completed',
                'notes' => 'Order Dapur Kue Bu Anik persiapan pesanan brownies & bolu',
            ]);

            SaleItem::create([
                'sale_id' => $sale2->id,
                'product_id' => $segt->id,
                'unit_id' => $units['SAK']->id,
                'quantity' => 1,
                'conversion_factor' => 25,
                'base_quantity' => 25,
                'unit_price' => 295000,
                'discount_amount' => 0,
                'subtotal' => 295000,
                'cogs_total' => $cogs1,
            ]);
            if ($batchSegt) { $batchSegt->decrement('available_quantity', 25); }

            SaleItem::create([
                'sale_id' => $sale2->id,
                'product_id' => $clt->id,
                'unit_id' => $units['PCS']->id,
                'quantity' => 2,
                'conversion_factor' => 1,
                'base_quantity' => 2,
                'unit_price' => 58000,
                'discount_amount' => 0,
                'subtotal' => 116000,
                'cogs_total' => $cogs2,
            ]);
            if ($batchClt) { $batchClt->decrement('available_quantity', 2); }

            SaleItem::create([
                'sale_id' => $sale2->id,
                'product_id' => $dus->id,
                'unit_id' => $units['PACK']->id,
                'quantity' => 1,
                'conversion_factor' => 50,
                'base_quantity' => 50,
                'unit_price' => 90000,
                'discount_amount' => 0,
                'subtotal' => 90000,
                'cogs_total' => $cogs3,
            ]);
            if ($batchDus) { $batchDus->decrement('available_quantity', 50); }

            CashTransaction::create([
                'transaction_number' => 'KAS-SALE-002',
                'type' => 'cash_in',
                'category' => 'Penjualan Transfer / QRIS',
                'amount' => $subtotal2,
                'transaction_date' => now()->subDays(1)->toDateString(),
                'payment_method' => 'transfer',
                'description' => "Penjualan Faktur {$inv2} (Dapur Kue Bu Anik)",
                'user_id' => $kasir->id,
            ]);
        }

        // Penjualan 3: Roti & Pastry Berkah Jaya Jombang (Grosir Partai Besar - Tempo Piutang 14 Hari)
        $inv3 = 'INV-' . now()->format('Ymd') . '-0003';
        if (!Sale::where('invoice_number', $inv3)->exists()) {
            $custRoti = $customers['CUST-003'];

            // 2 Sak Cakra Kembar (50 KG) + 1 Dus Margarin Blue Band (15 BKS) + 2 Bks Ragi Saf-Instant
            $batchCkr = InventoryBatch::where('product_id', $ckr->id)->where('available_quantity', '>=', 50)->orderBy('received_date')->first();
            $batchBb = InventoryBatch::where('product_id', $bb->id)->where('available_quantity', '>=', 15)->orderBy('received_date')->first();
            $batchSaf = InventoryBatch::where('product_id', $saf->id)->where('available_quantity', '>=', 2)->orderBy('received_date')->first();

            $cogs1 = 50 * ($batchCkr ? $batchCkr->cost_price : 11500);
            $cogs2 = 15 * ($batchBb ? $batchBb->cost_price : 38000);
            $cogs3 = 2 * ($batchSaf ? $batchSaf->cost_price : 41000);
            $totalCogs3 = $cogs1 + $cogs2 + $cogs3;

            // Harga Grosir: 2 Sak Cakra (Rp 600.000) + 1 Dus Margarin (Rp 595.000) + 2 Bks Ragi @ 44.500 (89.000) = Rp 1.284.000
            $totalAmount3 = 600000 + 595000 + 89000; // 1.284.000
            $dpAmount = 500000; // Bayar DP tunai 500.000, sisa 784.000 piutang tempo 14 hari

            $sale3 = Sale::create([
                'invoice_number' => $inv3,
                'customer_id' => $custRoti->id,
                'user_id' => $admin->id,
                'sale_date' => now()->setHour(10)->setMinute(0),
                'price_type' => 'wholesale',
                'subtotal' => $totalAmount3,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $totalAmount3,
                'total_cogs' => $totalCogs3,
                'payment_method' => 'credit',
                'payment_status' => 'partial',
                'due_date' => now()->addDays(14)->toDateString(),
                'paid_amount' => $dpAmount,
                'change_amount' => 0,
                'status' => 'completed',
                'notes' => 'Order grosir pabrik roti Berkah Jaya Jombang - DP Rp 500.000 sisa tempo 14 hari',
            ]);

            SaleItem::create([
                'sale_id' => $sale3->id,
                'product_id' => $ckr->id,
                'unit_id' => $units['SAK']->id,
                'quantity' => 2,
                'conversion_factor' => 25,
                'base_quantity' => 50,
                'unit_price' => 300000,
                'discount_amount' => 0,
                'subtotal' => 600000,
                'cogs_total' => $cogs1,
            ]);
            if ($batchCkr) { $batchCkr->decrement('available_quantity', 50); }

            SaleItem::create([
                'sale_id' => $sale3->id,
                'product_id' => $bb->id,
                'unit_id' => $units['DUS']->id,
                'quantity' => 1,
                'conversion_factor' => 15,
                'base_quantity' => 15,
                'unit_price' => 595000,
                'discount_amount' => 0,
                'subtotal' => 595000,
                'cogs_total' => $cogs2,
            ]);
            if ($batchBb) { $batchBb->decrement('available_quantity', 15); }

            SaleItem::create([
                'sale_id' => $sale3->id,
                'product_id' => $saf->id,
                'unit_id' => $units['BKS']->id,
                'quantity' => 2,
                'conversion_factor' => 1,
                'base_quantity' => 2,
                'unit_price' => 44500,
                'discount_amount' => 0,
                'subtotal' => 89000,
                'cogs_total' => $cogs3,
            ]);
            if ($batchSaf) { $batchSaf->decrement('available_quantity', 2); }

            // Create Receivable for Remaining Balance
            Receivable::create([
                'sale_id' => $sale3->id,
                'customer_id' => $custRoti->id,
                'total_amount' => $totalAmount3,
                'paid_amount' => $dpAmount,
                'remaining_balance' => $totalAmount3 - $dpAmount,
                'due_date' => now()->addDays(14)->toDateString(),
                'status' => 'partial',
            ]);

            CashTransaction::create([
                'transaction_number' => 'KAS-SALE-003',
                'type' => 'cash_in',
                'category' => 'Uang Muka (DP) Penjualan Grosir',
                'amount' => $dpAmount,
                'transaction_date' => now()->toDateString(),
                'payment_method' => 'cash',
                'description' => "DP Penjualan Faktur {$inv3} (Roti Berkah Jaya Jombang)",
                'user_id' => $admin->id,
            ]);
        }

        // 11. Sample Purchase from Supplier (Pembelian Restock Tepung ke Distributor Bogasari Jombang)
        $poNum = 'PO-' . now()->subDays(3)->format('Ymd') . '-0001';
        if (!Purchase::where('purchase_number', $poNum)->exists()) {
            $supBogasari = $suppliers['SUP-001'];
            $totalPO = 20 * 255000; // 20 Sak Segitiga Biru @ 255.000 = Rp 5.100.000

            $purchase = Purchase::create([
                'purchase_number' => $poNum,
                'supplier_id' => $supBogasari->id,
                'warehouse_id' => $wh2->id,
                'user_id' => $admin->id,
                'purchase_date' => now()->subDays(3)->toDateString(),
                'invoice_number' => 'BGS-JBG/2026/X/084',
                'subtotal' => $totalPO,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $totalPO,
                'paid_amount' => $totalPO,
                'payment_status' => 'paid',
                'status' => 'received',
                'received_at' => now()->subDays(3),
                'notes' => 'Penerimaan restock 20 Sak Segitiga Biru dari Distributor Jombang',
            ]);

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $segt->id,
                'unit_id' => $units['SAK']->id,
                'quantity' => 20,
                'conversion_factor' => 25,
                'base_quantity' => 500,
                'unit_price' => 255000,
                'base_unit_cost' => 10200,
                'discount_amount' => 0,
                'subtotal' => $totalPO,
                'batch_number' => 'BATCH-SGT-2610-02',
                'expiry_date' => now()->addMonths(12)->toDateString(),
                'warehouse_location_id' => $locTepung->id,
            ]);
        }
    }
}
