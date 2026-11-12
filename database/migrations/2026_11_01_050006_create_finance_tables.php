<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Payments (Riwayat Pembayaran & Pelunasan Cicilan)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->enum('payment_type', [
                'sale_payment',
                'purchase_payment',
                'receivable_installment',
                'payable_installment'
            ])->index();
            $table->unsignedBigInteger('reference_id')->nullable()->index(); // sale_id or purchase_id
            $table->string('reference_type')->nullable(); // Sale model or Purchase model
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->default('cash'); // cash, transfer, qris, etc.
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        // 2. Receivables (Buku Piutang Pelanggan)
        Schema::create('receivables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_balance', 15, 2);
            $table->date('due_date');
            $table->enum('status', ['unpaid', 'partial', 'paid', 'overdue'])->default('unpaid');
            $table->timestamps();

            $table->index(['customer_id', 'status']);
        });

        // 3. Payables (Buku Hutang Supplier)
        Schema::create('payables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_balance', 15, 2);
            $table->date('due_date');
            $table->enum('status', ['unpaid', 'partial', 'paid', 'overdue'])->default('unpaid');
            $table->timestamps();

            $table->index(['supplier_id', 'status']);
        });

        // 4. Cash Transactions (Buku Kas Masuk & Kas Keluar)
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->enum('type', ['cash_in', 'cash_out'])->index();
            $table->string('category'); // Modal Awal, Operasional, Prive, Pendapatan Lain, dll
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->string('payment_method')->default('cash');
            $table->text('description')->nullable();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        // 5. Operational Expenses (Beban Operasional Toko)
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->string('category'); // Listrik & Air, Gaji Karyawan, Sewa Tempat, ATK & Perlengkapan, Pemasaran, Perawatan, Lainnya
            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            $table->string('payment_method')->default('cash');
            $table->foreignId('cash_transaction_id')->nullable()->constrained('cash_transactions')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['expense_date', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('cash_transactions');
        Schema::dropIfExists('payables');
        Schema::dropIfExists('receivables');
        Schema::dropIfExists('payments');
    }
};

