<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table): void {
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

            // A chave estrangeira autorreferente é criada após a tabela.
            // Isso evita falha do PostgreSQL ao validar a referência durante o CREATE TABLE.
            $table->uuid('parent_id')->nullable();

            $table->string('code', 30);
            $table->string('name', 150);
            $table->text('description')->nullable();

            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();

            $table->unsignedSmallInteger('display_order')->default(0);

            $table->jsonb('settings')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->string('status', 30)->default('active');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(
                ['organization_id', 'code'],
                'departments_organization_code_unique'
            );

            $table->index('organization_id');
            $table->index('company_id');
            $table->index('branch_id');
            $table->index('parent_id');
            $table->index('name');
            $table->index('status');
            $table->index(['organization_id', 'status']);
        });

        Schema::table('departments', function (Blueprint $table): void {
            $table->foreign('parent_id', 'departments_parent_id_foreign')
                ->references('id')
                ->on('departments')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
