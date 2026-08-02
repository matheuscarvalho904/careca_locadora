<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('legal_name', 200);
            $table->string('trade_name', 200)->nullable();

            $table->string('cnpj', 14);
            $table->string('state_registration', 30)->nullable();
            $table->string('municipal_registration', 30)->nullable();

            $table->string('legal_nature', 150)->nullable();
            $table->string('company_size', 50)->nullable();

            $table->decimal('share_capital', 15, 2)
                ->nullable();

            $table->date('opened_at')->nullable();

            $table->string('main_cnae_code', 10)->nullable();
            $table->string('main_cnae_description', 255)->nullable();

            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();

            $table->string('logo_path', 500)->nullable();

            $table->string('tax_regime', 50)->nullable();
            $table->boolean('simple_national')->default(false);
            $table->boolean('mei')->default(false);

            $table->jsonb('secondary_cnaes')->nullable();
            $table->jsonb('partners')->nullable();
            $table->jsonb('external_data')->nullable();
            $table->jsonb('settings')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->timestampTz('external_data_synced_at')->nullable();

            $table->string('registration_status', 50)->nullable();

            $table->string('status', 30)
                ->default('active');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(
                ['organization_id', 'cnpj'],
                'companies_organization_cnpj_unique'
            );

            $table->index('organization_id');
            $table->index('legal_name');
            $table->index('trade_name');
            $table->index('registration_status');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};