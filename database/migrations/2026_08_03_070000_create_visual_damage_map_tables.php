<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_diagram_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('asset_category_id')
                ->nullable()
                ->constrained('asset_categories')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('code', 80);
            $table->string('name', 160);
            $table->string('asset_type', 40)->default('vehicle');
            $table->string('status', 30)->default('active');
            $table->boolean('is_default')->default(false);
            $table->jsonb('metadata')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(
                ['organization_id', 'code'],
                'inspection_diagram_templates_org_code_unique'
            );

            $table->index(
                ['organization_id', 'asset_category_id', 'status'],
                'inspection_diagram_templates_org_category_status_idx'
            );
        });

        Schema::create('inspection_diagram_views', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('template_id')
                ->constrained('inspection_diagram_templates')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('code', 40);
            $table->string('name', 100);
            $table->string('image_path', 500);
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->jsonb('metadata')->nullable();

            $table->timestampsTz();

            $table->unique(
                ['template_id', 'code'],
                'inspection_diagram_views_template_code_unique'
            );
        });

        Schema::create('rental_damage_marks', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('asset_id')
                ->constrained('assets')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('template_view_id')
                ->constrained('inspection_diagram_views')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->nullableUuidMorphs('inspectable');

            /*
             * Referência histórica opcional à avaria original da entrega.
             * Mantida sem FK para evitar dependência autorreferencial durante
             * a criação da tabela e permitir importação/restauração flexível.
             */
            $table->uuid('source_damage_mark_id')->nullable();

            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->decimal('position_x', 7, 4);
            $table->decimal('position_y', 7, 4);

            $table->string('vehicle_part', 120)->nullable();
            $table->string('damage_type', 50);
            $table->string('severity', 30)->default('light');
            $table->string('condition', 30)->default('preexisting');
            $table->string('status', 30)->default('active');

            $table->decimal('estimated_value', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(
                'source_damage_mark_id',
                'rental_damage_marks_source_idx'
            );

            $table->index(
                ['organization_id', 'asset_id', 'status'],
                'rental_damage_marks_org_asset_status_idx'
            );

            $table->index(
                ['inspectable_type', 'inspectable_id', 'condition'],
                'rental_damage_marks_inspectable_condition_idx'
            );
        });

        Schema::create('rental_damage_photos', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('damage_mark_id')
                ->constrained('rental_damage_marks')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('file_path', 500);
            $table->string('caption', 200)->nullable();
            $table->unsignedSmallInteger('display_order')->default(0);

            $table->timestampsTz();

            $table->index(
                ['damage_mark_id', 'display_order'],
                'rental_damage_photos_mark_order_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_damage_photos');
        Schema::dropIfExists('rental_damage_marks');
        Schema::dropIfExists('inspection_diagram_views');
        Schema::dropIfExists('inspection_diagram_templates');
    }
};
