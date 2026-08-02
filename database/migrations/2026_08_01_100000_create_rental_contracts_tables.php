<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_contracts', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('reservation_id')
                ->nullable()
                ->constrained('rental_reservations')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignUuid('company_id')
                ->nullable()
                ->constrained('companies')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignUuid('branch_id')
                ->nullable()
                ->constrained('branches')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignUuid('cost_center_id')
                ->nullable()
                ->constrained('cost_centers')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignUuid('business_partner_id')
                ->constrained('business_partners')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('authorized_contact_id')
                ->nullable()
                ->constrained('business_partner_contacts')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignUuid('responsible_user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('number', 40);
            $table->string('status', 40)->default('draft');

            $table->timestampTz('starts_at');
            $table->timestampTz('ends_at');
            $table->timestampTz('signed_at')->nullable();
            $table->timestampTz('activated_at')->nullable();
            $table->timestampTz('closed_at')->nullable();
            $table->timestampTz('cancelled_at')->nullable();

            $table->string('pickup_location', 255)->nullable();
            $table->string('return_location', 255)->nullable();

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('additional_value', 15, 2)->default(0);
            $table->decimal('deposit_value', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);

            $table->text('terms')->nullable();
            $table->text('commercial_notes')->nullable();
            $table->text('operational_notes')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->jsonb('metadata')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(
                ['organization_id', 'number'],
                'rental_contracts_org_number_unique'
            );

            $table->unique(
                ['organization_id', 'reservation_id'],
                'rental_contracts_org_reservation_unique'
            );

            $table->index(
                ['organization_id', 'status', 'starts_at'],
                'rental_contracts_org_status_starts_idx'
            );
        });

        Schema::create('rental_contract_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('contract_id')
                ->constrained('rental_contracts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignUuid('asset_id')
                ->constrained('assets')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->timestampTz('starts_at');
            $table->timestampTz('ends_at');

            $table->string('billing_unit', 30)->default('daily');
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_value', 15, 2)->default(0);
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('additional_value', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);

            $table->decimal('initial_odometer', 15, 2)->nullable();
            $table->decimal('initial_hourmeter', 15, 2)->nullable();
            $table->decimal('final_odometer', 15, 2)->nullable();
            $table->decimal('final_hourmeter', 15, 2)->nullable();

            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestampsTz();

            $table->unique(
                ['contract_id', 'asset_id'],
                'rental_contract_items_contract_asset_unique'
            );

            $table->index(
                ['organization_id', 'asset_id', 'starts_at', 'ends_at'],
                'rental_contract_items_asset_period_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_contract_items');
        Schema::dropIfExists('rental_contracts');
    }
};
