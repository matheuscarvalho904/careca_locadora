<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('default_company_id')
                ->nullable()
                ->constrained('companies')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignUuid('default_branch_id')
                ->nullable()
                ->constrained('branches')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignUuid('department_id')
                ->nullable()
                ->constrained('departments')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignUuid('cost_center_id')
                ->nullable()
                ->constrained('cost_centers')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('name', 150);
            $table->string('email', 150);
            $table->timestampTz('email_verified_at')->nullable();

            $table->string('cpf', 11)->nullable();

            $table->string('phone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();

            $table->string('job_title', 150)->nullable();
            $table->string('employee_code', 50)->nullable();

            $table->string('avatar_path', 500)->nullable();

            $table->string('password');
            $table->rememberToken();

            $table->string('locale', 10)->default('pt_BR');
            $table->string('timezone', 60)->default('America/Cuiaba');

            $table->boolean('must_change_password')->default(false);
            $table->boolean('is_platform_admin')->default(false);

            $table->timestampTz('invited_at')->nullable();
            $table->timestampTz('activated_at')->nullable();
            $table->timestampTz('blocked_at')->nullable();
            $table->timestampTz('last_login_at')->nullable();
            $table->timestampTz('password_changed_at')->nullable();

            $table->string('last_login_ip', 45)->nullable();
            $table->text('last_login_user_agent')->nullable();

            $table->jsonb('preferences')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->string('status', 30)->default('invited');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique('email');

            $table->unique(
                ['organization_id', 'cpf'],
                'users_organization_cpf_unique'
            );

            $table->unique(
                ['organization_id', 'employee_code'],
                'users_organization_employee_code_unique'
            );

            $table->index('organization_id');
            $table->index('default_company_id');
            $table->index('default_branch_id');
            $table->index('department_id');
            $table->index('cost_center_id');
            $table->index('name');
            $table->index('status');
            $table->index('last_login_at');
            $table->index(['organization_id', 'status']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email', 150)->primary();
            $table->string('token');
            $table->timestampTz('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};