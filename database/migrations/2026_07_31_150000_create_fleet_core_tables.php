<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name', 120);
            $table->string('prefix', 10);
            $table->string('asset_type', 30)->default('vehicle');
            $table->string('meter_type', 20)->default('odometer');
            $table->boolean('requires_plate')->default(true);
            $table->boolean('requires_renavam')->default(true);
            $table->boolean('requires_chassis')->default(true);
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->string('status', 30)->default('active');
            $table->jsonb('metadata')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['organization_id', 'prefix'], 'asset_categories_org_prefix_unique');
            $table->unique(['organization_id', 'name'], 'asset_categories_org_name_unique');
            $table->index(['organization_id', 'status']);
        });

        Schema::create('assets', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('cost_center_id')->nullable()->constrained('cost_centers')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('category_id')->constrained('asset_categories')->cascadeOnUpdate()->restrictOnDelete();

            $table->string('prefix', 30);
            $table->string('internal_code', 50)->nullable();
            $table->string('name', 180);
            $table->string('asset_type', 30)->default('vehicle');

            $table->string('plate', 10)->nullable();
            $table->string('renavam', 20)->nullable();
            $table->string('chassis', 40)->nullable();

            $table->string('brand', 100)->nullable();
            $table->string('model', 150)->nullable();
            $table->string('version', 150)->nullable();
            $table->unsignedSmallInteger('manufacture_year')->nullable();
            $table->unsignedSmallInteger('model_year')->nullable();
            $table->string('color', 50)->nullable();

            $table->string('fuel_type', 40)->nullable();
            $table->string('transmission', 40)->nullable();
            $table->unsignedSmallInteger('seats')->nullable();

            $table->string('meter_type', 20)->default('odometer');
            $table->decimal('current_odometer', 15, 2)->default(0);
            $table->decimal('current_hourmeter', 15, 2)->default(0);

            $table->string('ownership_type', 30)->default('owned');
            $table->string('operational_status', 40)->default('available');
            $table->string('rental_status', 40)->default('available');

            $table->date('acquisition_date')->nullable();
            $table->decimal('acquisition_value', 15, 2)->nullable();
            $table->date('sale_date')->nullable();
            $table->decimal('sale_value', 15, 2)->nullable();

            $table->string('tracker_provider', 120)->nullable();
            $table->string('tracker_identifier', 120)->nullable();

            $table->text('notes')->nullable();
            $table->jsonb('external_data')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestampTz('external_data_synced_at')->nullable();

            $table->string('status', 30)->default('active');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['organization_id', 'prefix'], 'assets_org_prefix_unique');
            $table->unique(['organization_id', 'plate'], 'assets_org_plate_unique');
            $table->unique(['organization_id', 'renavam'], 'assets_org_renavam_unique');
            $table->unique(['organization_id', 'chassis'], 'assets_org_chassis_unique');
            $table->index(['organization_id', 'operational_status']);
            $table->index(['organization_id', 'rental_status']);
            $table->index(['company_id', 'branch_id']);
            $table->index('name');
        });

        Schema::create('asset_documents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('asset_id')->constrained('assets')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('number', 100)->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->string('status', 30)->default('valid');
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->index(['asset_id', 'type']);
            $table->index('expires_at');
        });

        Schema::create('asset_photos', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('asset_id')->constrained('assets')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('type', 50)->default('general');
            $table->string('file_path', 500);
            $table->string('caption', 200)->nullable();
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestampsTz();

            $table->index(['asset_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_photos');
        Schema::dropIfExists('asset_documents');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_categories');
    }
};
