<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('barcode')->unique()->nullable();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->text('description')->nullable();

            // Satuan default
            $table->foreignId('base_unit_id')->constrained('units')->restrictOnDelete(); // e.g. PCS, KG
            $table->foreignId('purchase_unit_id')->nullable()->constrained('units')->nullOnDelete(); // e.g. PACK, DUS, SAK
            $table->foreignId('selling_unit_id')->nullable()->constrained('units')->nullOnDelete();

            // Harga default (per satuan dasar atau satuan jual default)
            $table->decimal('buy_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->decimal('wholesale_price', 15, 2)->default(0);

            $table->decimal('min_stock', 12, 2)->default(10);
            $table->decimal('weight', 10, 2)->nullable(); // gram / kg
            $table->foreignId('warehouse_location_id')->nullable()->constrained('warehouse_locations')->nullOnDelete();
            $table->boolean('has_expiry')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Product Units (Konversi Multi-Satuan)
        // Contoh: Produk Paku base PCS (factor=1). PACK (factor=100) -> 1 PACK = 100 PCS.
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('conversion_factor', 12, 4)->default(1); // 1 PACK = factor * base unit
            $table->decimal('buy_price', 15, 2)->nullable();
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->decimal('wholesale_price', 15, 2)->nullable();
            $table->boolean('is_base')->default(false);
            $table->timestamps();

            $table->unique(['product_id', 'unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_units');
        Schema::dropIfExists('products');
    }
};

