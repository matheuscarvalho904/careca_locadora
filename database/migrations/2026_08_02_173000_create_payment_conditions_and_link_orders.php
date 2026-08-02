<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_conditions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('code', 40);
            $table->string('name', 120);
            $table->unsignedSmallInteger('installments')->default(1);
            $table->unsignedSmallInteger('first_due_days')->default(0);
            $table->unsignedSmallInteger('interval_days')->default(30);
            $table->boolean('requires_down_payment')->default(false);
            $table->decimal('down_payment_percent', 7, 4)->default(0);
            $table->string('status', 30)->default('active');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(
                ['organization_id', 'code'],
                'payment_conditions_org_code_unique'
            );
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->foreignUuid('payment_condition_id')
                ->nullable()
                ->after('payment_method')
                ->constrained('payment_conditions')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        Schema::table('service_orders', function (Blueprint $table): void {
            $table->foreignUuid('payment_condition_id')
                ->nullable()
                ->after('payment_method')
                ->constrained('payment_conditions')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('payment_condition_id');
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('payment_condition_id');
        });

        Schema::dropIfExists('payment_conditions');
    }
};
