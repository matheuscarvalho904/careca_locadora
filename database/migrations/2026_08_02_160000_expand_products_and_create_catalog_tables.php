<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('code', 40);
            $table->string('name', 150);
            $table->string('status', 30)->default('active');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(
                ['organization_id', 'code'],
                'product_categories_org_code_unique'
            );

            $table->index(
                ['organization_id', 'name'],
                'product_categories_org_name_idx'
            );
        });

        Schema::create('product_brands', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('name', 120);
            $table->string('status', 30)->default('active');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(
                ['organization_id', 'name'],
                'product_brands_org_name_unique'
            );
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->foreignUuid('category_id')
                ->nullable()
                ->after('unit_id')
                ->constrained('product_categories')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignUuid('brand_id')
                ->nullable()
                ->after('category_id')
                ->constrained('product_brands')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignUuid('default_warehouse_id')
                ->nullable()
                ->after('brand_id')
                ->constrained('warehouses')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignUuid('primary_supplier_id')
                ->nullable()
                ->after('default_warehouse_id')
                ->constrained('business_partners')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignUuid('default_application_center_id')
                ->nullable()
                ->after('primary_supplier_id')
                ->constrained('application_centers')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignUuid('default_cost_center_id')
                ->nullable()
                ->after('default_application_center_id')
                ->constrained('cost_centers')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('sku', 80)->nullable()->after('code');
            $table->string('barcode', 80)->nullable()->after('sku');
            $table->string('manufacturer_code', 100)->nullable()->after('barcode');
            $table->string('short_name', 100)->nullable()->after('name');
            $table->string('product_type', 50)->default('product')->after('short_name');
            $table->text('description')->nullable()->after('product_type');

            $table->decimal('minimum_stock', 15, 4)->default(0);
            $table->decimal('maximum_stock', 15, 4)->default(0);
            $table->decimal('reorder_point', 15, 4)->default(0);
            $table->decimal('minimum_purchase_quantity', 15, 4)->default(0);
            $table->decimal('purchase_multiple', 15, 4)->default(1);
            $table->unsignedInteger('lead_time_days')->default(0);

            $table->boolean('allow_negative_stock')->default(false);
            $table->boolean('batch_controlled')->default(false);
            $table->boolean('expiry_controlled')->default(false);
            $table->boolean('serial_controlled')->default(false);
            $table->boolean('asset_controlled')->default(false);

            $table->decimal('average_cost', 15, 4)->default(0);
            $table->decimal('last_purchase_cost', 15, 4)->default(0);
            $table->decimal('target_cost', 15, 4)->default(0);

            $table->string('financial_category', 120)->nullable();
            $table->string('accounting_account', 80)->nullable();
            $table->string('economic_result', 120)->nullable();

            $table->string('ncm', 20)->nullable();
            $table->string('cest', 20)->nullable();
            $table->string('origin_code', 10)->nullable();

            $table->string('image_path')->nullable();
            $table->string('technical_sheet_path')->nullable();
            $table->text('notes')->nullable();

            $table->index(
                ['organization_id', 'category_id', 'status'],
                'products_org_category_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['default_warehouse_id']);
            $table->dropForeign(['primary_supplier_id']);
            $table->dropForeign(['default_application_center_id']);
            $table->dropForeign(['default_cost_center_id']);

            $table->dropIndex('products_org_category_status_idx');

            $table->dropColumn([
                'category_id',
                'brand_id',
                'default_warehouse_id',
                'primary_supplier_id',
                'default_application_center_id',
                'default_cost_center_id',
                'sku',
                'barcode',
                'manufacturer_code',
                'short_name',
                'product_type',
                'description',
                'minimum_stock',
                'maximum_stock',
                'reorder_point',
                'minimum_purchase_quantity',
                'purchase_multiple',
                'lead_time_days',
                'allow_negative_stock',
                'batch_controlled',
                'expiry_controlled',
                'serial_controlled',
                'asset_controlled',
                'average_cost',
                'last_purchase_cost',
                'target_cost',
                'financial_category',
                'accounting_account',
                'economic_result',
                'ncm',
                'cest',
                'origin_code',
                'image_path',
                'technical_sheet_path',
                'notes',
            ]);
        });

        Schema::dropIfExists('product_brands');
        Schema::dropIfExists('product_categories');
    }
};
