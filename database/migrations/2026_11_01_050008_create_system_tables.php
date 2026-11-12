<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // min_stock, expired_batch, overdue_receivable, overdue_payable, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
        });

        // 2. Activity Logs (Audit Trail)
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('role')->nullable();
            $table->string('action'); // login, logout, create, update, delete, void, sale, etc.
            $table->string('module'); // Auth, POS, Inventory, Master Data, Finance, Settings, etc.
            $table->text('description');
            $table->json('properties')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['module', 'action', 'created_at']);
        });

        // 3. System Settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, number, boolean, json, text
            $table->string('group')->default('general'); // general, pos, invoice, inventory
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('notifications');
    }
};

