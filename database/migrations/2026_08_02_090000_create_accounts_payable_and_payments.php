<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name', 150);
            $table->string('status', 30)->default('active');
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unique(['organization_id', 'name'], 'expense_categories_org_name_unique');
        });

        Schema::create('accounts_payable', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUuid('supplier_id')->constrained('business_partners')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('cost_center_id')->nullable()->constrained('cost_centers')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUuid('department_id')->nullable()->constrained('departments')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUuid('asset_id')->nullable()->constrained('assets')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUuid('expense_category_id')->nullable()->constrained('expense_categories')->cascadeOnUpdate()->nullOnDelete();
            $table->string('number', 40);
            $table->string('document_number', 80)->nullable();
            $table->string('status', 40)->default('draft');
            $table->date('issued_at')->nullable();
            $table->date('competence_date')->nullable();
            $table->date('due_at');
            $table->timestampTz('approved_at')->nullable();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->decimal('original_value', 15, 2)->default(0);
            $table->decimal('interest_value', 15, 2)->default(0);
            $table->decimal('penalty_value', 15, 2)->default(0);
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('paid_value', 15, 2)->default(0);
            $table->decimal('open_value', 15, 2)->default(0);
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unique(['organization_id', 'number'], 'accounts_payable_org_number_unique');
            $table->index(['organization_id', 'status', 'due_at'], 'accounts_payable_org_status_due_idx');
        });

        Schema::create('financial_payments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('account_payable_id')->constrained('accounts_payable')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('supplier_id')->constrained('business_partners')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('financial_account_id')->nullable()->constrained('financial_accounts')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUuid('reversed_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('number', 40);
            $table->string('status', 30)->default('confirmed');
            $table->timestampTz('paid_at');
            $table->timestampTz('reversed_at')->nullable();
            $table->string('payment_method', 50);
            $table->string('payment_reference', 180)->nullable();
            $table->decimal('principal_value', 15, 2)->default(0);
            $table->decimal('interest_value', 15, 2)->default(0);
            $table->decimal('penalty_value', 15, 2)->default(0);
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('additional_value', 15, 2)->default(0);
            $table->decimal('total_paid', 15, 2)->default(0);
            $table->string('proof_path')->nullable();
            $table->text('notes')->nullable();
            $table->text('reversal_reason')->nullable();
            $table->timestampsTz();
            $table->unique(['organization_id', 'number'], 'financial_payments_org_number_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_payments');
        Schema::dropIfExists('accounts_payable');
        Schema::dropIfExists('expense_categories');
    }
};
