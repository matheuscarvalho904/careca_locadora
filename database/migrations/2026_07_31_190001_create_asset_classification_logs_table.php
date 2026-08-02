<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_classification_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignUuid('asset_id')
                ->nullable()
                ->constrained('assets')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignUuid('rule_id')
                ->nullable()
                ->constrained('asset_classification_rules')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignUuid('suggested_category_id')
                ->nullable()
                ->constrained('asset_categories')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignUuid('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('plate', 10)->nullable();
            $table->unsignedSmallInteger('confidence')->default(0);
            $table->boolean('auto_applied')->default(false);
            $table->jsonb('matched_fields')->nullable();
            $table->jsonb('source_data')->nullable();
            $table->timestampTz('classified_at');
            $table->timestampsTz();

            $table->index(['organization_id', 'classified_at'], 'asset_class_logs_org_date_idx');
            $table->index(['organization_id', 'plate'], 'asset_class_logs_org_plate_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_classification_logs');
    }
};
