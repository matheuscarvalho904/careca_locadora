<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->string('payment_method', 60)->nullable()->after('expected_delivery_at');
            $table->string('payment_condition', 120)->nullable()->after('payment_method');
            $table->date('first_due_date')->nullable()->after('payment_condition');
            $table->unsignedSmallInteger('installments')->default(1)->after('first_due_date');
            $table->unsignedSmallInteger('installment_interval_days')->default(30)->after('installments');
            $table->string('delivery_location', 180)->nullable()->after('installment_interval_days');
            $table->text('supplier_notes')->nullable()->after('delivery_location');
            $table->text('internal_notes')->nullable()->after('supplier_notes');
        });

        Schema::table('service_orders', function (Blueprint $table): void {
            $table->string('payment_method', 60)->nullable()->after('expected_execution_at');
            $table->string('payment_condition', 120)->nullable()->after('payment_method');
            $table->date('first_due_date')->nullable()->after('payment_condition');
            $table->unsignedSmallInteger('installments')->default(1)->after('first_due_date');
            $table->unsignedSmallInteger('installment_interval_days')->default(30)->after('installments');
            $table->text('supplier_notes')->nullable()->after('installment_interval_days');
            $table->text('internal_notes')->nullable()->after('supplier_notes');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->dropColumn([
                'payment_method',
                'payment_condition',
                'first_due_date',
                'installments',
                'installment_interval_days',
                'delivery_location',
                'supplier_notes',
                'internal_notes',
            ]);
        });

        Schema::table('service_orders', function (Blueprint $table): void {
            $table->dropColumn([
                'payment_method',
                'payment_condition',
                'first_due_date',
                'installments',
                'installment_interval_days',
                'supplier_notes',
                'internal_notes',
            ]);
        });
    }
};
