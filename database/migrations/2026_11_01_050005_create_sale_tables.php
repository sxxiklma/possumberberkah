<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Sales (Transaksi Penjualan / POS)
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('sale_date');
            $table->enum('price_type', ['retail', 'wholesale'])->default('retail');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('total_cogs', 15, 2)->default(0); // Total HPP FIFO aktual
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('change_amount', 15, 2)->default(0);
            $table->string('payment_method')->default('cash'); // cash, transfer, qris, debit, tempo, etc.
            $table->enum('payment_status', ['paid', 'partial', 'dp', 'unpaid', 'overdue'])->default('paid');
            $table->date('due_date')->nullable(); // Tanggal jatuh tempo jika tempo / piutang
            $table->enum('status', ['completed', 'void', 'returned'])->default('completed');
            $table->text('notes')->nullable();
            $table->foreignId('void_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('void_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sale_date', 'status']);
        });

        // 2. Sale Items
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->decimal('conversion_factor', 12, 4)->default(1);
            $table->decimal('base_quantity', 12, 2); // Kuantitas dalam satuan dasar
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('cogs_total', 15, 2)->default(0); // HPP total untuk baris ini
            $table->timestamps();
        });

        // 3. Sale Item Batches (Rincian Batch FIFO untuk setiap item yang terjual)
        Schema::create('sale_item_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_item_id')->constrained('sale_items')->cascadeOnDelete();
            $table->foreignId('inventory_batch_id')->constrained('inventory_batches')->restrictOnDelete();
            $table->decimal('quantity_taken', 12, 2); // Kuantitas terambil dalam base unit
            $table->decimal('cost_price', 15, 2); // Harga modal per base unit dari batch tersebut
            $table->decimal('subtotal_cogs', 15, 2); // quantity_taken * cost_price
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_item_batches');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};

