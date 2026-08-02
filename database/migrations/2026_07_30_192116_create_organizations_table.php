<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->string('name', 150);
            $table->string('slug', 150)->unique();

            $table->string('legal_name', 200)->nullable();
            $table->string('document', 20)->nullable();

            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();

            $table->string('timezone', 60)
                ->default('America/Cuiaba');

            $table->string('locale', 10)
                ->default('pt_BR');

            $table->char('currency', 3)
                ->default('BRL');

            $table->string('logo_path', 500)->nullable();
            $table->string('favicon_path', 500)->nullable();

            $table->jsonb('settings')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->string('status', 30)
                ->default('active');

            $table->timestampTz('trial_ends_at')->nullable();
            $table->timestampTz('suspended_at')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique('document');
            $table->index('name');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};