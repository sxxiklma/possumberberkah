<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Brands
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Units (Satuan Produk)
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. PCS, PACK, DUS, BOX, KG, GRAM, LITER, METER, ROLL, SAK, BOTOL
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 4. Warehouses (Gudang)
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. GUDANG-A
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Warehouse Locations (Rak/Baris/Kolom)
        Schema::create('warehouse_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('code')->unique(); // e.g. GUD-A/RAK-01/B-02
            $table->string('area')->nullable();
            $table->string('rack')->nullable();
            $table->string('row')->nullable();
            $table->string('column')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 6. Suppliers (Pemasok)
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 7. Customers (Pelanggan)
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->enum('category', ['umum', 'retail', 'grosir', 'reseller', 'pelanggan_tetap'])->default('umum');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('warehouse_locations');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('units');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
    }
};

