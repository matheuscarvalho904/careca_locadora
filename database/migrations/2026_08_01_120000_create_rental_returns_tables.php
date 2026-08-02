<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_returns', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('contract_id')
                ->constrained('rental_contracts')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('delivery_id')
                ->constrained('rental_deliveries')
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
            $table->timestampTz('returned_at')->nullable();

            $table->string('customer_signer_name', 180)->nullable();
            $table->string('employee_signer_name', 180)->nullable();
            $table->string('customer_signature_path', 500)->nullable();
            $table->string('employee_signature_path', 500)->nullable();

            $table->decimal('extra_time_value', 15, 2)->default(0);
            $table->decimal('mileage_value', 15, 2)->default(0);
            $table->decimal('fuel_value', 15, 2)->default(0);
            $table->decimal('damage_value', 15, 2)->default(0);
            $table->decimal('cleaning_value', 15, 2)->default(0);
            $table->decimal('missing_accessories_value', 15, 2)->default(0);
            $table->decimal('other_value', 15, 2)->default(0);
            $table->decimal('total_charge_value', 15, 2)->default(0);

            $table->jsonb('photos')->nullable();
            $table->text('general_notes')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(
                ['organization_id', 'number'],
                'rental_returns_org_number_unique'
            );

            $table->unique(
                ['organization_id', 'contract_id'],
                'rental_returns_org_contract_unique'
            );

            $table->unique(
                ['organization_id', 'delivery_id'],
                'rental_returns_org_delivery_unique'
            );

            $table->index(
                ['organization_id', 'status', 'scheduled_at'],
                'rental_returns_org_status_scheduled_idx'
            );
        });

        Schema::create('rental_return_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('return_id')
                ->constrained('rental_returns')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignUuid('delivery_item_id')
                ->constrained('rental_delivery_items')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('contract_item_id')
                ->constrained('rental_contract_items')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('asset_id')
                ->constrained('assets')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->decimal('initial_odometer', 15, 2)->nullable();
            $table->decimal('final_odometer', 15, 2)->nullable();
            $table->decimal('distance_used', 15, 2)->nullable();

            $table->decimal('initial_hourmeter', 15, 2)->nullable();
            $table->decimal('final_hourmeter', 15, 2)->nullable();
            $table->decimal('hours_used', 15, 2)->nullable();

            $table->string('initial_fuel_level', 30)->nullable();
            $table->string('final_fuel_level', 30)->nullable();

            $table->boolean('body_ok')->default(true);
            $table->boolean('tires_ok')->default(true);
            $table->boolean('lights_ok')->default(true);
            $table->boolean('glass_ok')->default(true);
            $table->boolean('documents_ok')->default(true);
            $table->boolean('accessories_ok')->default(true);
            $table->boolean('cleanliness_ok')->default(true);
            $table->boolean('primary_key_returned')->default(true);
            $table->boolean('spare_key_returned')->default(false);
            $table->boolean('manual_returned')->default(false);

            $table->decimal('extra_time_value', 15, 2)->default(0);
            $table->decimal('mileage_value', 15, 2)->default(0);
            $table->decimal('fuel_value', 15, 2)->default(0);
            $table->decimal('damage_value', 15, 2)->default(0);
            $table->decimal('cleaning_value', 15, 2)->default(0);
            $table->decimal('missing_accessories_value', 15, 2)->default(0);
            $table->decimal('other_value', 15, 2)->default(0);
            $table->decimal('total_charge_value', 15, 2)->default(0);

            $table->text('new_damage_notes')->nullable();
            $table->text('missing_accessories_notes')->nullable();
            $table->text('other_charge_notes')->nullable();
            $table->jsonb('photos')->nullable();
            $table->jsonb('checklist')->nullable();
            $table->timestampsTz();

            $table->unique(
                ['return_id', 'delivery_item_id'],
                'rental_return_items_return_delivery_item_unique'
            );

            $table->index(
                ['organization_id', 'asset_id'],
                'rental_return_items_org_asset_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_return_items');
        Schema::dropIfExists('rental_returns');
    }
};
