<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('number_sequences', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignUuid('company_id')
                ->nullable()
                ->constrained('companies')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignUuid('branch_id')
                ->nullable()
                ->constrained('branches')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
             * Identificador funcional da sequência.
             *
             * Exemplos:
             * rental_contract
             * reservation
             * vehicle
             * work_order
             * purchase_request
             * purchase_order
             */
            $table->string('key', 100);

            $table->string('name', 150);

            $table->string('prefix', 30)->nullable();
            $table->string('suffix', 30)->nullable();

            $table->unsignedBigInteger('current_number')->default(0);
            $table->unsignedBigInteger('increment_by')->default(1);

            $table->unsignedSmallInteger('padding')->default(6);

            $table->string('reset_period', 30)->default('never');

            $table->unsignedSmallInteger('reference_year')->nullable();
            $table->unsignedSmallInteger('reference_month')->nullable();
            $table->unsignedSmallInteger('reference_day')->nullable();

            $table->string('format', 150)
                ->default('{prefix}{number}');

            $table->boolean('is_locked')->default(false);

            $table->string('status', 30)->default('active');

            $table->jsonb('metadata')->nullable();

            $table->timestampsTz();

            $table->unique(
                [
                    'organization_id',
                    'company_id',
                    'branch_id',
                    'key',
                ],
                'number_sequences_scope_key_unique'
            );

            $table->index('organization_id');
            $table->index('company_id');
            $table->index('branch_id');
            $table->index('key');
            $table->index('status');

            $table->index(
                ['organization_id', 'key', 'status'],
                'number_sequences_org_key_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('number_sequences');
    }
};