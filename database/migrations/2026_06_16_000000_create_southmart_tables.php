<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Branches Table
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('location');
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        // 2. Categories Table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });

        // 3. Products Table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->unique();
            $table->string('sku')->unique();
            $table->string('name');
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->decimal('buy_price', 12, 2);
            $table->decimal('sell_price', 12, 2);
            $table->text('image_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Users Table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'kasir'])->default('kasir');
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->rememberToken();
            $table->timestamps();
        });

        // 5. Inventory Table
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('branch_id')->index();
            $table->integer('stock')->default(0);
            $table->integer('minimum_stock')->default(10);
            $table->timestamps();
        });

        // 6. Stock Movements Table
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('branch_id')->index();
            $table->enum('type', ['in', 'out', 'mutation']);
            $table->integer('quantity');
            $table->string('reference'); // e.g. "POS Sale TX-100", "Supplier Restock", etc.
            $table->timestamps();
        });

        // 7. Transactions Table
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique();
            $table->unsignedBigInteger('branch_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->decimal('total_price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);
            $table->string('payment_status')->default('completed');
            $table->enum('sync_status', ['synced', 'pending'])->default('pending');
            $table->timestamps();
        });

        // 8. Transaction Details Table
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->integer('quantity');
            $table->decimal('price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        // 9. Payments Table
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->index();
            $table->string('method'); // tunai, qris, debit, kredit, transfer
            $table->decimal('amount_paid', 12, 2);
            $table->decimal('amount_change', 12, 2)->default(0);
            $table->timestamps();
        });

        // 10. Receipts Table
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->index();
            $table->string('receipt_code')->unique();
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();
        });

        // 11. Node Status Table (Central monitoring)
        Schema::create('node_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->index();
            $table->string('node_status')->default('online'); // online, offline
            $table->string('db_status')->default('online');   // online, offline
            $table->timestamp('last_sync')->nullable();
            $table->timestamps();
        });

        // 12. Replication Logs Table
        Schema::create('replication_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->index();
            $table->string('table_name');
            $table->integer('records_sent')->default(0);
            $table->integer('records_received')->default(0);
            $table->string('status'); // success, failed
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        // 13. Synchronization Logs Table
        Schema::create('synchronization_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->index();
            $table->string('action'); // push, pull
            $table->integer('records_synced')->default(0);
            $table->string('status'); // success, failed
            $table->timestamps();
        });

        // 14. Consistency Checks Table
        Schema::create('consistency_checks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->index();
            $table->string('table_name');
            $table->integer('branch_count');
            $table->integer('central_count');
            $table->boolean('is_consistent');
            $table->decimal('percentage', 5, 2);
            $table->timestamps();
        });

        // 15. Reports Table
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type'); // sales, replication, consistency, status
            $table->string('file_path');
            $table->timestamps();
        });

        // 16. Activity Logs Table
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('activity');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Default Reset Tokens & Sessions for completeness
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('consistency_checks');
        Schema::dropIfExists('synchronization_logs');
        Schema::dropIfExists('replication_logs');
        Schema::dropIfExists('node_status');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('transaction_details');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory');
        Schema::dropIfExists('users');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('branches');
    }
};
