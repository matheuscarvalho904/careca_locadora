<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_classification_rules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignUuid('category_id')
                ->constrained('asset_categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('name', 160);
            $table->string('brand_pattern', 120)->nullable();
            $table->string('model_pattern', 180)->nullable();
            $table->string('vehicle_type_pattern', 120)->nullable();
            $table->string('body_type_pattern', 120)->nullable();
            $table->string('segment_pattern', 120)->nullable();
            $table->string('subsegment_pattern', 120)->nullable();
            $table->jsonb('keywords')->nullable();
            $table->string('meter_type', 30)->default('odometer');
            $table->unsignedSmallInteger('priority')->default(100);
            $table->unsignedSmallInteger('minimum_confidence')->default(70);
            $table->boolean('auto_apply')->default(false);
            $table->string('status', 30)->default('active');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['organization_id', 'status', 'priority'], 'asset_class_rules_org_status_priority_idx');
            $table->index(['organization_id', 'category_id'], 'asset_class_rules_org_category_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_classification_rules');
    }
};
