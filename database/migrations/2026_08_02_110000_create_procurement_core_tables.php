<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->string('name', 80);
            $table->string('symbol', 20);
            $table->string('status', 30)->default('active');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unique(['organization_id', 'symbol']);
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('code', 40);
            $table->string('name', 180);
            $table->boolean('stock_controlled')->default(true);
            $table->string('status', 30)->default('active');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unique(['organization_id', 'code']);
        });

        Schema::create('services', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('code', 40);
            $table->string('name', 180);
            $table->decimal('reference_value', 15, 4)->default(0);
            $table->string('status', 30)->default('active');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unique(['organization_id', 'code']);
        });

        Schema::create('warehouses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('code', 40);
            $table->string('name', 150);
            $table->string('status', 30)->default('active');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unique(['organization_id', 'code']);
        });

        Schema::create('application_centers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignUuid('cost_center_id')->nullable()->constrained('cost_centers')->nullOnDelete();
            $table->foreignUuid('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('code', 40);
            $table->string('name', 150);
            $table->string('type', 50)->default('general');
            $table->string('status', 30)->default('active');
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unique(['organization_id', 'code']);
        });

        Schema::create('purchase_orders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('supplier_id')->constrained('business_partners')->restrictOnDelete();
            $table->uuid('purchase_request_id')->nullable();
            $table->uuid('quotation_id')->nullable();
            $table->string('number', 40);
            $table->string('origin_type', 40)->default('direct');
            $table->string('status', 40)->default('draft');
            $table->date('issued_at')->nullable();
            $table->date('expected_delivery_at')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('freight_value', 15, 2)->default(0);
            $table->decimal('additional_value', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->unique(['organization_id', 'number']);
        });

        Schema::create('purchase_order_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignUuid('application_center_id')->nullable()->constrained('application_centers')->nullOnDelete();
            $table->foreignUuid('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->foreignUuid('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignUuid('cost_center_id')->nullable()->constrained('cost_centers')->nullOnDelete();
            $table->string('application_type', 40);
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_value', 15, 4)->default(0);
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestampsTz();
        });

        Schema::create('service_orders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('supplier_id')->constrained('business_partners')->restrictOnDelete();
            $table->uuid('service_request_id')->nullable();
            $table->uuid('quotation_id')->nullable();
            $table->string('number', 40);
            $table->string('origin_type', 40)->default('direct');
            $table->string('status', 40)->default('draft');
            $table->date('issued_at')->nullable();
            $table->date('expected_execution_at')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('additional_value', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->unique(['organization_id', 'number']);
        });

        Schema::create('service_order_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('service_order_id')->constrained('service_orders')->cascadeOnDelete();
            $table->foreignUuid('service_id')->constrained('services')->restrictOnDelete();
            $table->foreignUuid('application_center_id')->nullable()->constrained('application_centers')->nullOnDelete();
            $table->foreignUuid('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->foreignUuid('cost_center_id')->nullable()->constrained('cost_centers')->nullOnDelete();
            $table->string('application_type', 40);
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_value', 15, 4)->default(0);
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_items');
        Schema::dropIfExists('service_orders');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('application_centers');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('services');
        Schema::dropIfExists('products');
        Schema::dropIfExists('units');
    }
};
