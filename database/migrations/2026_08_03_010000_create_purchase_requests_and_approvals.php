<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignUuid('requester_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignUuid('cost_center_id')->nullable()->constrained('cost_centers')->nullOnDelete();

            $table->string('number', 40);
            $table->string('priority', 20)->default('normal');
            $table->string('status', 40)->default('draft');
            $table->date('requested_at')->nullable();
            $table->date('needed_at')->nullable();
            $table->text('justification');
            $table->text('internal_notes')->nullable();
            $table->decimal('estimated_total', 15, 2)->default(0);
            $table->timestampTz('submitted_at')->nullable();
            $table->timestampTz('approved_at')->nullable();
            $table->timestampTz('rejected_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['organization_id', 'number']);
            $table->index(['organization_id', 'status', 'requested_at']);
        });

        Schema::create('purchase_request_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('purchase_request_id')->constrained('purchase_requests')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignUuid('application_center_id')->nullable()->constrained('application_centers')->nullOnDelete();
            $table->foreignUuid('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->foreignUuid('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignUuid('cost_center_id')->nullable()->constrained('cost_centers')->nullOnDelete();

            $table->string('application_type', 40);
            $table->decimal('quantity', 15, 4);
            $table->decimal('estimated_unit_value', 15, 4)->default(0);
            $table->decimal('estimated_total', 15, 2)->default(0);
            $table->string('meter_type', 30)->nullable();
            $table->decimal('meter_reading', 15, 2)->nullable();
            $table->timestampTz('meter_recorded_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();
        });

        Schema::create('approval_flows', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            $table->string('name', 150);
            $table->string('document_type', 50)->default('purchase_request');
            $table->decimal('minimum_value', 15, 2)->default(0);
            $table->decimal('maximum_value', 15, 2)->nullable();
            $table->string('status', 30)->default('active');
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::create('approval_flow_steps', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('approval_flow_id')->constrained('approval_flows')->cascadeOnDelete();
            $table->foreignUuid('approver_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approver_role', 100)->nullable();
            $table->unsignedSmallInteger('sequence');
            $table->boolean('required')->default(true);
            $table->timestampsTz();

            $table->unique(['approval_flow_id', 'sequence']);
        });

        Schema::create('approval_instances', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('approval_flow_id')->nullable()->constrained('approval_flows')->nullOnDelete();
            $table->uuidMorphs('approvable');
            $table->string('status', 30)->default('pending');
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('finished_at')->nullable();
            $table->timestampsTz();

            $table->index(['organization_id', 'status']);
        });

        Schema::create('approval_instance_steps', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('approval_instance_id')->constrained('approval_instances')->cascadeOnDelete();
            $table->foreignUuid('approver_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approver_role', 100)->nullable();
            $table->unsignedSmallInteger('sequence');
            $table->string('status', 30)->default('pending');
            $table->text('decision_notes')->nullable();
            $table->timestampTz('decided_at')->nullable();
            $table->timestampsTz();

            $table->unique(['approval_instance_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_instance_steps');
        Schema::dropIfExists('approval_instances');
        Schema::dropIfExists('approval_flow_steps');
        Schema::dropIfExists('approval_flows');
        Schema::dropIfExists('purchase_request_items');
        Schema::dropIfExists('purchase_requests');
    }
};
