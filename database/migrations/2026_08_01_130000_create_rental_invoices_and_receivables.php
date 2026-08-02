<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_invoices', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('contract_id')
                ->constrained('rental_contracts')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('return_id')
                ->nullable()
                ->constrained('rental_returns')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignUuid('business_partner_id')
                ->constrained('business_partners')
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

            $table->string('number', 40);
            $table->string('status', 40)->default('draft');
            $table->date('issued_at')->nullable();
            $table->date('due_at')->nullable();
            $table->date('competence_date')->nullable();

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('additional_value', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->decimal('received_value', 15, 2)->default(0);
            $table->decimal('open_value', 15, 2)->default(0);

            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(
                ['organization_id', 'number'],
                'rental_invoices_org_number_unique'
            );

            $table->unique(
                ['organization_id', 'contract_id'],
                'rental_invoices_org_contract_unique'
            );

            $table->index(
                ['organization_id', 'status', 'due_at'],
                'rental_invoices_org_status_due_idx'
            );
        });

        Schema::create('rental_invoice_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('invoice_id')
                ->constrained('rental_invoices')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignUuid('asset_id')
                ->nullable()
                ->constrained('assets')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('type', 50);
            $table->string('description', 255);
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_value', 15, 2)->default(0);
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('additional_value', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestampsTz();

            $table->index(
                ['organization_id', 'invoice_id'],
                'rental_invoice_items_org_invoice_idx'
            );
        });

        Schema::create('accounts_receivable', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('rental_invoice_id')
                ->constrained('rental_invoices')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('business_partner_id')
                ->constrained('business_partners')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('number', 40);
            $table->unsignedInteger('installment_number')->default(1);
            $table->unsignedInteger('installments_count')->default(1);
            $table->string('status', 40)->default('open');

            $table->date('issued_at');
            $table->date('due_at');
            $table->timestampTz('paid_at')->nullable();

            $table->decimal('original_value', 15, 2)->default(0);
            $table->decimal('interest_value', 15, 2)->default(0);
            $table->decimal('penalty_value', 15, 2)->default(0);
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('paid_value', 15, 2)->default(0);
            $table->decimal('open_value', 15, 2)->default(0);

            $table->string('payment_method', 50)->nullable();
            $table->string('payment_reference', 180)->nullable();
            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(
                ['organization_id', 'number'],
                'accounts_receivable_org_number_unique'
            );

            $table->unique(
                ['rental_invoice_id', 'installment_number'],
                'accounts_receivable_invoice_installment_unique'
            );

            $table->index(
                ['organization_id', 'status', 'due_at'],
                'accounts_receivable_org_status_due_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts_receivable');
        Schema::dropIfExists('rental_invoice_items');
        Schema::dropIfExists('rental_invoices');
    }
};
