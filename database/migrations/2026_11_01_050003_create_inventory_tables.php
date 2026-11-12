<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Inventory Batches (FIFO Persediaan)
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->index();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('warehouse_location_id')->nullable()->constrained('warehouse_locations')->nullOnDelete();
            $table->unsignedBigInteger('purchase_item_id')->nullable()->index();

            // Jumlah dalam satuan dasar (base unit)
            $table->decimal('initial_quantity', 12, 2);
            $table->decimal('available_quantity', 12, 2);
            $table->decimal('cost_price', 15, 2); // Harga modal per satuan dasar

            $table->date('received_date');
            $table->date('expiry_date')->nullable()->index();
            $table->enum('status', ['active', 'depleted', 'expired', 'quarantined'])->default('active');
            $table->timestamps();

            $table->index(['product_id', 'status', 'received_date']);
        });

        // 2. Stock Movements (Kartu Stok & Riwayat Mutasi Lengkap)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('inventory_batch_id')->nullable()->constrained('inventory_batches')->nullOnDelete();
            $table->enum('type', [
                'initial_stock',
                'purchase_receive',
                'sale',
                'sale_return',
                'purchase_return',
                'stock_out',
                'opname_adjustment',
                'void_reversal'
            ])->index();
            $table->string('reference_number')->nullable()->index(); // PO, INV, OUT, OPNAME number
            $table->decimal('quantity', 12, 2); // Nilai mutasi (+ masuk, - keluar) dalam base unit
            $table->decimal('quantity_before', 12, 2);
            $table->decimal('quantity_after', 12, 2);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory_batches');
    }
};

