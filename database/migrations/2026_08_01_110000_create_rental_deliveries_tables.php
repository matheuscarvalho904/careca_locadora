<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_deliveries', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('contract_id')
                ->constrained('rental_contracts')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('responsible_user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('number', 40);
            $table->string('status', 30)->default('draft');
            $table->timestampTz('scheduled_at')->nullable();
            $table->timestampTz('delivered_at')->nullable();

            $table->string('customer_signer_name', 180)->nullable();
            $table->string('employee_signer_name', 180)->nullable();
            $table->string('customer_signature_path', 500)->nullable();
            $table->string('employee_signature_path', 500)->nullable();

            $table->jsonb('photos')->nullable();
            $table->text('general_notes')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(
                ['organization_id', 'number'],
                'rental_deliveries_org_number_unique'
            );

            $table->unique(
                ['organization_id', 'contract_id'],
                'rental_deliveries_org_contract_unique'
            );

            $table->index(
                ['organization_id', 'status', 'scheduled_at'],
                'rental_deliveries_org_status_scheduled_idx'
            );
        });

        Schema::create('rental_delivery_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('delivery_id')
                ->constrained('rental_deliveries')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignUuid('contract_item_id')
                ->constrained('rental_contract_items')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('asset_id')
                ->constrained('assets')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->decimal('odometer', 15, 2)->nullable();
            $table->decimal('hourmeter', 15, 2)->nullable();
            $table->string('fuel_level', 30)->nullable();

            $table->boolean('body_ok')->default(true);
            $table->boolean('tires_ok')->default(true);
            $table->boolean('lights_ok')->default(true);
            $table->boolean('glass_ok')->default(true);
            $table->boolean('documents_ok')->default(true);
            $table->boolean('accessories_ok')->default(true);
            $table->boolean('cleanliness_ok')->default(true);

            $table->boolean('primary_key_delivered')->default(true);
            $table->boolean('spare_key_delivered')->default(false);
            $table->boolean('manual_delivered')->default(false);

            $table->text('existing_damage_notes')->nullable();
            $table->text('accessories_notes')->nullable();
            $table->jsonb('photos')->nullable();
            $table->jsonb('checklist')->nullable();
            $table->timestampsTz();

            $table->unique(
                ['delivery_id', 'contract_item_id'],
                'rental_delivery_items_delivery_contract_item_unique'
            );

            $table->index(
                ['organization_id', 'asset_id'],
                'rental_delivery_items_org_asset_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_delivery_items');
        Schema::dropIfExists('rental_deliveries');
    }
};
