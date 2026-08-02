<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('company_id')
                ->constrained('companies')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('code', 20);
            $table->string('name', 150);

            $table->string('legal_name', 200)->nullable();
            $table->string('trade_name', 200)->nullable();

            $table->string('cnpj', 14)->nullable();
            $table->string('state_registration', 30)->nullable();
            $table->string('municipal_registration', 30)->nullable();

            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();

            $table->string('postal_code', 8)->nullable();
            $table->string('street', 200)->nullable();
            $table->string('number', 30)->nullable();
            $table->string('complement', 150)->nullable();
            $table->string('district', 150)->nullable();
            $table->string('city', 150)->nullable();
            $table->char('state', 2)->nullable();
            $table->string('country', 100)->default('Brasil');

            $table->string('ibge_code', 10)->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->string('timezone', 60)
                ->default('America/Cuiaba');

            $table->boolean('is_headquarters')->default(false);
            $table->boolean('allows_rentals')->default(true);
            $table->boolean('allows_maintenance')->default(true);
            $table->boolean('allows_inventory')->default(true);

            $table->jsonb('external_data')->nullable();
            $table->jsonb('settings')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->timestampTz('external_data_synced_at')->nullable();

            $table->string('status', 30)
                ->default('active');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(
                ['organization_id', 'code'],
                'branches_organization_code_unique'
            );

            $table->unique(
                ['organization_id', 'cnpj'],
                'branches_organization_cnpj_unique'
            );

            $table->index('organization_id');
            $table->index('company_id');
            $table->index(['company_id', 'status']);
            $table->index(['city', 'state']);
            $table->index('name');
            $table->index('is_headquarters');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};