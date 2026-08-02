<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_accounts', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('name', 180);
            $table->string('type', 40)->default('bank');
            $table->string('bank_name', 120)->nullable();
            $table->string('agency', 40)->nullable();
            $table->string('account_number', 60)->nullable();
            $table->string('pix_key', 180)->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->boolean('is_default')->default(false);
            $table->string('status', 30)->default('active');
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(
                ['organization_id', 'name'],
                'financial_accounts_org_name_unique'
            );
        });

        Schema::create('financial_receipts', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('account_receivable_id')
                ->constrained('accounts_receivable')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('rental_invoice_id')
                ->nullable()
                ->constrained('rental_invoices')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignUuid('business_partner_id')
                ->constrained('business_partners')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('financial_account_id')
                ->nullable()
                ->constrained('financial_accounts')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignUuid('reversed_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('number', 40);
            $table->string('status', 30)->default('confirmed');
            $table->timestampTz('received_at');
            $table->timestampTz('reversed_at')->nullable();

            $table->string('payment_method', 50);
            $table->string('payment_reference', 180)->nullable();

            $table->decimal('principal_value', 15, 2)->default(0);
            $table->decimal('interest_value', 15, 2)->default(0);
            $table->decimal('penalty_value', 15, 2)->default(0);
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('additional_value', 15, 2)->default(0);
            $table->decimal('total_received', 15, 2)->default(0);

            $table->string('proof_path')->nullable();
            $table->text('notes')->nullable();
            $table->text('reversal_reason')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->timestampsTz();

            $table->unique(
                ['organization_id', 'number'],
                'financial_receipts_org_number_unique'
            );

            $table->index(
                ['organization_id', 'status', 'received_at'],
                'financial_receipts_org_status_date_idx'
            );
        });

        Schema::create('cash_movements', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('financial_account_id')
                ->nullable()
                ->constrained('financial_accounts')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignUuid('financial_receipt_id')
                ->nullable()
                ->constrained('financial_receipts')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignUuid('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('type', 20);
            $table->string('status', 30)->default('posted');
            $table->timestampTz('occurred_at');
            $table->decimal('value', 15, 2);
            $table->string('description', 255);
            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestampsTz();

            $table->index(
                ['organization_id', 'financial_account_id', 'occurred_at'],
                'cash_movements_org_account_date_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
        Schema::dropIfExists('financial_receipts');
        Schema::dropIfExists('financial_accounts');
    }
};
