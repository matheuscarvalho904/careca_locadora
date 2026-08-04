<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignUuid('purchase_request_id')->nullable()->constrained('purchase_requests')->nullOnDelete();
            $table->foreignUuid('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('number', 40);
            $table->string('status', 40)->default('draft');
            $table->date('issued_at')->nullable();
            $table->date('response_deadline')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('estimated_total', 15, 2)->default(0);
            $table->timestampTz('closed_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['organization_id', 'number']);
            $table->index(['organization_id', 'status', 'issued_at']);
        });

        Schema::create('quotation_suppliers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->foreignUuid('supplier_id')->constrained('business_partners')->restrictOnDelete();
            $table->foreignUuid('payment_condition_id')->nullable()->constrained('payment_conditions')->nullOnDelete();

            $table->string('status', 40)->default('invited');
            $table->date('proposal_date')->nullable();
            $table->date('proposal_valid_until')->nullable();
            $table->unsignedSmallInteger('delivery_days')->nullable();
            $table->decimal('freight_value', 15, 2)->default(0);
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('additional_value', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->unique(['quotation_id', 'supplier_id']);
        });

        Schema::create('quotation_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->foreignUuid('purchase_request_item_id')->nullable()->constrained('purchase_request_items')->nullOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignUuid('application_center_id')->nullable()->constrained('application_centers')->nullOnDelete();
            $table->foreignUuid('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->foreignUuid('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignUuid('cost_center_id')->nullable()->constrained('cost_centers')->nullOnDelete();

            $table->string('application_type', 40);
            $table->decimal('quantity', 15, 4);
            $table->text('notes')->nullable();
            $table->timestampsTz();
        });

        Schema::create('quotation_supplier_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('quotation_supplier_id')->constrained('quotation_suppliers')->cascadeOnDelete();
            $table->foreignUuid('quotation_item_id')->constrained('quotation_items')->cascadeOnDelete();

            $table->decimal('unit_value', 15, 4)->default(0);
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->unsignedSmallInteger('delivery_days')->nullable();
            $table->boolean('is_selected')->default(false);
            $table->text('selection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->unique(['quotation_supplier_id', 'quotation_item_id']);
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->foreign('purchase_request_id')
                ->references('id')
                ->on('purchase_requests')
                ->nullOnDelete();

            $table->foreign('quotation_id')
                ->references('id')
                ->on('quotations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->dropForeign(['purchase_request_id']);
            $table->dropForeign(['quotation_id']);
        });

        Schema::dropIfExists('quotation_supplier_items');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotation_suppliers');
        Schema::dropIfExists('quotations');
    }
};
