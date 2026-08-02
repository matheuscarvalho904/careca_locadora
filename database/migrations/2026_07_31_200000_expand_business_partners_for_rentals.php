<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_partners', function (Blueprint $table): void {
            $table->string('customer_segment', 80)->nullable();
            $table->string('risk_level', 30)->default('normal');
            $table->unsignedSmallInteger('internal_score')->nullable();
            $table->boolean('credit_blocked')->default(false);
            $table->string('credit_block_reason', 255)->nullable();
            $table->string('preferred_payment_method', 80)->nullable();
            $table->string('billing_email', 150)->nullable();
            $table->string('billing_phone', 20)->nullable();
            $table->timestampTz('last_rental_at')->nullable();
            $table->timestampTz('last_contact_at')->nullable();
            $table->timestampTz('next_follow_up_at')->nullable();
        });

        Schema::table('business_partner_contacts', function (Blueprint $table): void {
            $table->boolean('can_withdraw_assets')->default(false);
            $table->boolean('can_return_assets')->default(false);
            $table->boolean('can_sign_contracts')->default(false);
            $table->string('cpf', 11)->nullable();
            $table->string('document_number', 40)->nullable();
        });
    }

    public function down(): void
    {
        // Migração aditiva para produção.
    }
};
