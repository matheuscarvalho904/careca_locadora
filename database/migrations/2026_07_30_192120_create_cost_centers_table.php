<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_centers', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('company_id')
                ->nullable()
                ->constrained('companies')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('branch_id')
                ->nullable()
                ->constrained('branches')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('department_id')
                ->nullable()
                ->constrained('departments')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // A chave estrangeira autorreferente é criada após a tabela.
            $table->uuid('parent_id')->nullable();

            $table->string('code', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();

            $table->unsignedSmallInteger('level')->default(1);
            $table->unsignedSmallInteger('display_order')->default(0);

            $table->boolean('allows_posting')->default(true);

            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();

            $table->jsonb('settings')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->string('status', 30)->default('active');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(
                ['organization_id', 'code'],
                'cost_centers_organization_code_unique'
            );

            $table->index('organization_id');
            $table->index('company_id');
            $table->index('branch_id');
            $table->index('department_id');
            $table->index('parent_id');
            $table->index('name');
            $table->index('status');
            $table->index(['organization_id', 'status']);
        });

        Schema::table('cost_centers', function (Blueprint $table): void {
            $table->foreign('parent_id', 'cost_centers_parent_id_foreign')
                ->references('id')
                ->on('cost_centers')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_centers');
    }
};
