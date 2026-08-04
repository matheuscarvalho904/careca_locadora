<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table): void {
            $table->decimal('received_quantity', 15, 4)->default(0)->after('quantity');
        });

        Schema::create('purchase_receipts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('purchase_order_id')->constrained('purchase_orders')->restrictOnDelete();
            $table->foreignUuid('supplier_id')->constrained('business_partners')->restrictOnDelete();
            $table->foreignUuid('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignUuid('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('number', 40);
            $table->string('status', 30)->default('draft');
            $table->timestampTz('received_at')->nullable();
            $table->string('invoice_number', 80)->nullable();
            $table->string('invoice_series', 30)->nullable();
            $table->string('invoice_access_key', 60)->nullable();
            $table->date('invoice_issued_at')->nullable();
            $table->string('xml_path')->nullable();
            $table->string('attachment_path')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('freight_value', 15, 2)->default(0);
            $table->decimal('additional_value', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestampTz('confirmed_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unique(['organization_id', 'number']);
            $table->index(['organization_id', 'status', 'received_at']);
        });

        Schema::create('purchase_receipt_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('purchase_receipt_id')->constrained('purchase_receipts')->cascadeOnDelete();
            $table->foreignUuid('purchase_order_item_id')->constrained('purchase_order_items')->restrictOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignUuid('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->decimal('ordered_quantity', 15, 4);
            $table->decimal('previous_received_quantity', 15, 4)->default(0);
            $table->decimal('received_quantity', 15, 4);
            $table->decimal('pending_quantity', 15, 4)->default(0);
            $table->decimal('unit_value', 15, 4)->default(0);
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->string('batch_number', 80)->nullable();
            $table->date('expires_at')->nullable();
            $table->string('serial_number', 120)->nullable();
            $table->boolean('accepted')->default(true);
            $table->string('divergence_type', 50)->nullable();
            $table->text('divergence_notes')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->unique(['purchase_receipt_id', 'purchase_order_item_id'], 'receipt_order_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_receipt_items');
        Schema::dropIfExists('purchase_receipts');
        Schema::table('purchase_order_items', fn (Blueprint $table) => $table->dropColumn('received_quantity'));
    }
};
