<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_branches', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignUuid('company_id')
                ->constrained('companies')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignUuid('branch_id')
                ->constrained('branches')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->boolean('is_default')->default(false);

            $table->string('status', 30)->default('active');

            $table->timestampTz('access_starts_at')->nullable();
            $table->timestampTz('access_ends_at')->nullable();

            $table->jsonb('settings')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->timestampsTz();

            $table->unique(
                ['user_id', 'branch_id'],
                'user_branches_user_branch_unique'
            );

            $table->index('organization_id');
            $table->index('user_id');
            $table->index('company_id');
            $table->index('branch_id');
            $table->index('status');

            $table->index(
                ['organization_id', 'user_id', 'status'],
                'user_branches_org_user_status_index'
            );

            $table->index(
                ['company_id', 'branch_id'],
                'user_branches_company_branch_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_branches');
    }
};