<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Stock Outs (Barang Keluar Non-Penjualan)
        Schema::create('stock_outs', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->date('date');
            $table->enum('reason', [
                'damaged',      // Rusak
                'lost',         // Hilang
                'internal_use', // Pemakaian Internal Toko
                'sample',       // Sampel
                'adjustment',   // Penyesuaian
                'expired'       // Kedaluwarsa
            ])->index();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Stock Out Items
        Schema::create('stock_out_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_out_id')->constrained('stock_outs')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->decimal('conversion_factor', 12, 4)->default(1);
            $table->decimal('base_quantity', 12, 2);
            $table->decimal('cogs_total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Returns (Retur Penjualan & Retur Pembelian)
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->enum('type', ['sale_return', 'purchase_return'])->index();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->date('return_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('reason');
            $table->enum('status', ['completed', 'cancelled'])->default('completed');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        // 4. Return Items
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('returns')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->decimal('conversion_factor', 12, 4)->default(1);
            $table->decimal('base_quantity', 12, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });

        // 5. Stock Opnames (Pemeriksaan Fisik & Penyesuaian Stok)
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('opname_number')->unique();
            $table->date('opname_date');
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->enum('status', ['draft', 'adjusted'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 6. Stock Opname Items
        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('system_quantity', 12, 2);
            $table->decimal('physical_quantity', 12, 2);
            $table->decimal('difference', 12, 2); // physical - system
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_difference_cost', 15, 2)->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('returns');
        Schema::dropIfExists('stock_out_items');
        Schema::dropIfExists('stock_outs');
    }
};

