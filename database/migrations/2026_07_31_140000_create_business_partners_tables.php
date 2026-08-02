<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_partners', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('code', 30);
            $table->string('person_type', 20)->default('legal');
            $table->jsonb('roles');

            $table->string('legal_name', 200);
            $table->string('trade_name', 200)->nullable();
            $table->string('document', 14)->nullable();

            $table->string('state_registration', 30)->nullable();
            $table->string('municipal_registration', 30)->nullable();
            $table->string('registration_status', 50)->nullable();
            $table->string('main_cnae_code', 20)->nullable();
            $table->string('main_cnae_description', 255)->nullable();
            $table->date('opened_at')->nullable();

            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();

            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->unsignedSmallInteger('payment_term_days')->default(0);
            $table->string('payment_condition', 100)->nullable();

            $table->string('company_size', 30)->nullable();
            $table->string('status', 30)->default('active');
            $table->text('notes')->nullable();

            $table->jsonb('tags')->nullable();
            $table->jsonb('external_data')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestampTz('external_data_synced_at')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(
                ['organization_id', 'code'],
                'business_partners_organization_code_unique'
            );

            $table->unique(
                ['organization_id', 'document'],
                'business_partners_organization_document_unique'
            );

            $table->index(['organization_id', 'status']);
            $table->index('legal_name');
            $table->index('trade_name');
            $table->index('document');
        });

        Schema::create('business_partner_contacts', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('business_partner_id')
                ->constrained('business_partners')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('name', 150);
            $table->string('position', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->text('notes')->nullable();

            $table->timestampsTz();

            $table->index('business_partner_id');
            $table->index('name');
        });

        Schema::create('business_partner_addresses', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('business_partner_id')
                ->constrained('business_partners')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('type', 30)->default('main');
            $table->string('label', 100)->nullable();
            $table->string('postal_code', 8)->nullable();
            $table->string('address', 200)->nullable();
            $table->string('number', 20)->nullable();
            $table->string('complement', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->char('state', 2)->nullable();
            $table->boolean('is_primary')->default(false);

            $table->timestampsTz();

            $table->index('business_partner_id');
            $table->index(['city', 'state']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_partner_addresses');
        Schema::dropIfExists('business_partner_contacts');
        Schema::dropIfExists('business_partners');
    }
};
