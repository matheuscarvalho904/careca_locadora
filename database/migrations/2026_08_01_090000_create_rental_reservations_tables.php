<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_reservations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
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
            $table->timestampTz('reserved_at')->nullable();
            $table->timestampTz('pickup_expected_at');
            $table->timestampTz('return_expected_at');
            $table->timestampTz('pickup_actual_at')->nullable();
            $table->timestampTz('return_actual_at')->nullable();

            $table->string('pickup_location', 255)->nullable();
            $table->string('return_location', 255)->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('additional_value', 15, 2)->default(0);
            $table->decimal('deposit_value', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->text('commercial_notes')->nullable();
            $table->text('operational_notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(
                ['organization_id', 'number'],
                'rental_reservations_org_number_unique'
            );
            $table->index(
                ['organization_id', 'status', 'pickup_expected_at'],
                'rental_reservations_org_status_pickup_idx'
            );
            $table->index(
                ['organization_id', 'business_partner_id'],
                'rental_reservations_org_partner_idx'
            );
        });

        Schema::create('rental_reservation_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignUuid('reservation_id')
                ->constrained('rental_reservations')
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
            $table->decimal('expected_initial_odometer', 15, 2)->nullable();
            $table->decimal('expected_initial_hourmeter', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestampsTz();

            $table->unique(
                ['reservation_id', 'asset_id'],
                'rental_reservation_items_reservation_asset_unique'
            );
            $table->index(
                ['organization_id', 'asset_id', 'starts_at', 'ends_at'],
                'rental_reservation_items_asset_period_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_reservation_items');
        Schema::dropIfExists('rental_reservations');
    }
};
