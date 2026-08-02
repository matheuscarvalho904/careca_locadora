<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_brands', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name', 120);
            $table->string('country', 80)->nullable();
            $table->string('status', 30)->default('active');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['organization_id', 'name'], 'fleet_brands_org_name_unique');
            $table->index(['organization_id', 'status']);
        });

        Schema::create('fleet_models', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('brand_id')->constrained('fleet_brands')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name', 150);
            $table->string('vehicle_type', 60)->nullable();
            $table->string('status', 30)->default('active');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['organization_id', 'brand_id', 'name'], 'fleet_models_org_brand_name_unique');
            $table->index(['organization_id', 'status']);
        });

        Schema::create('fleet_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('model_id')->constrained('fleet_models')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name', 180);
            $table->string('engine', 80)->nullable();
            $table->string('fipe_code', 20)->nullable();
            $table->unsignedSmallInteger('year_from')->nullable();
            $table->unsignedSmallInteger('year_to')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['organization_id', 'model_id', 'name'], 'fleet_versions_org_model_name_unique');
            $table->index(['organization_id', 'status']);
            $table->index('fipe_code');
        });

        foreach ([
            'fuel_types',
            'transmission_types',
            'traction_types',
            'body_types',
            'vehicle_colors',
        ] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) use ($tableName): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnUpdate()->restrictOnDelete();
                $table->string('name', 100);
                $table->string('code', 30)->nullable();
                $table->unsignedSmallInteger('display_order')->default(0);

                if ($tableName === 'vehicle_colors') {
                    $table->string('hex_color', 20)->nullable();
                }

                $table->string('status', 30)->default('active');
                $table->timestampsTz();
                $table->softDeletesTz();

                $table->unique(['organization_id', 'name'], "{$tableName}_org_name_unique");
                $table->index(['organization_id', 'status']);
            });
        }

        Schema::table('assets', function (Blueprint $table): void {
            $table->foreignUuid('brand_id')->nullable()->after('chassis')->constrained('fleet_brands')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUuid('model_id')->nullable()->after('brand_id')->constrained('fleet_models')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUuid('version_id')->nullable()->after('model_id')->constrained('fleet_versions')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUuid('fuel_type_id')->nullable()->after('color')->constrained('fuel_types')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUuid('transmission_type_id')->nullable()->after('fuel_type_id')->constrained('transmission_types')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUuid('traction_type_id')->nullable()->after('transmission_type_id')->constrained('traction_types')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUuid('body_type_id')->nullable()->after('traction_type_id')->constrained('body_types')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUuid('color_id')->nullable()->after('body_type_id')->constrained('vehicle_colors')->cascadeOnUpdate()->nullOnDelete();

            $table->index(['organization_id', 'brand_id']);
            $table->index(['organization_id', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('color_id');
            $table->dropConstrainedForeignId('body_type_id');
            $table->dropConstrainedForeignId('traction_type_id');
            $table->dropConstrainedForeignId('transmission_type_id');
            $table->dropConstrainedForeignId('fuel_type_id');
            $table->dropConstrainedForeignId('version_id');
            $table->dropConstrainedForeignId('model_id');
            $table->dropConstrainedForeignId('brand_id');
        });

        Schema::dropIfExists('vehicle_colors');
        Schema::dropIfExists('body_types');
        Schema::dropIfExists('traction_types');
        Schema::dropIfExists('transmission_types');
        Schema::dropIfExists('fuel_types');
        Schema::dropIfExists('fleet_versions');
        Schema::dropIfExists('fleet_models');
        Schema::dropIfExists('fleet_brands');
    }
};
