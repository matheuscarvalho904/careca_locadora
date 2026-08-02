<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table): void {
            $table->string('meter_type', 30)
                ->nullable()
                ->after('asset_id');

            $table->decimal('meter_reading', 15, 2)
                ->nullable()
                ->after('meter_type');

            $table->timestampTz('meter_recorded_at')
                ->nullable()
                ->after('meter_reading');
        });

        Schema::table('service_order_items', function (Blueprint $table): void {
            $table->string('meter_type', 30)
                ->nullable()
                ->after('asset_id');

            $table->decimal('meter_reading', 15, 2)
                ->nullable()
                ->after('meter_type');

            $table->timestampTz('meter_recorded_at')
                ->nullable()
                ->after('meter_reading');
        });
    }

    public function down(): void
    {
        Schema::table('service_order_items', function (Blueprint $table): void {
            $table->dropColumn([
                'meter_type',
                'meter_reading',
                'meter_recorded_at',
            ]);
        });

        Schema::table('purchase_order_items', function (Blueprint $table): void {
            $table->dropColumn([
                'meter_type',
                'meter_reading',
                'meter_recorded_at',
            ]);
        });
    }
};
