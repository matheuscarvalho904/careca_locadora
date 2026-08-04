<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_balances', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->restrictOnDelete();

            $table->foreignUuid('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->foreignUuid('warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->decimal('quantity_on_hand', 15, 4)->default(0);
            $table->decimal('quantity_reserved', 15, 4)->default(0);
            $table->decimal('quantity_in_transit', 15, 4)->default(0);
            $table->decimal('average_cost', 15, 4)->default(0);
            $table->decimal('inventory_value', 15, 2)->default(0);
            $table->timestampTz('last_movement_at')->nullable();
            $table->timestampsTz();

            $table->unique(
                ['organization_id', 'product_id', 'warehouse_id'],
                'inventory_balances_org_product_warehouse_unique'
            );

            $table->index(
                ['organization_id', 'warehouse_id', 'quantity_on_hand'],
                'inventory_balances_org_warehouse_quantity_idx'
            );
        });

        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->restrictOnDelete();

            $table->foreignUuid('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->foreignUuid('warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->foreignUuid('purchase_receipt_id')
                ->nullable()
                ->constrained('purchase_receipts')
                ->nullOnDelete();

            $table->foreignUuid('purchase_receipt_item_id')
                ->nullable()
                ->constrained('purchase_receipt_items')
                ->nullOnDelete();

            $table->foreignUuid('purchase_order_id')
                ->nullable()
                ->constrained('purchase_orders')
                ->nullOnDelete();

            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('number', 40);
            $table->string('type', 30);
            $table->string('direction', 10);
            $table->string('source_type', 60)->nullable();
            $table->uuid('source_id')->nullable();

            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);

            $table->decimal('balance_before', 15, 4)->default(0);
            $table->decimal('balance_after', 15, 4)->default(0);
            $table->decimal('average_cost_before', 15, 4)->default(0);
            $table->decimal('average_cost_after', 15, 4)->default(0);

            $table->timestampTz('occurred_at');
            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestampsTz();

            $table->unique(['organization_id', 'number']);
            $table->unique(
                ['purchase_receipt_item_id', 'type'],
                'stock_movements_receipt_item_type_unique'
            );

            $table->index(
                ['organization_id', 'product_id', 'warehouse_id', 'occurred_at'],
                'stock_movements_kardex_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory_balances');
    }
};
