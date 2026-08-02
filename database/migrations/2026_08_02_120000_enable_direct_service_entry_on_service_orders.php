<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_order_items', function (Blueprint $table): void {
            $table->string('service_code', 60)
                ->nullable()
                ->after('service_id');

            $table->text('service_description')
                ->nullable()
                ->after('service_code');

            $table->foreignUuid('unit_id')
                ->nullable()
                ->after('service_description')
                ->constrained('units')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('financial_category', 120)
                ->nullable()
                ->after('cost_center_id');

            $table->string('economic_result', 120)
                ->nullable()
                ->after('financial_category');

            $table->string('purpose', 180)
                ->nullable()
                ->after('economic_result');
        });

        DB::statement(
            'ALTER TABLE service_order_items
             DROP CONSTRAINT IF EXISTS service_order_items_service_id_foreign'
        );

        DB::statement(
            'ALTER TABLE service_order_items
             ALTER COLUMN service_id DROP NOT NULL'
        );

        DB::statement(
            'ALTER TABLE service_order_items
             ADD CONSTRAINT service_order_items_service_id_foreign
             FOREIGN KEY (service_id)
             REFERENCES services(id)
             ON UPDATE CASCADE
             ON DELETE SET NULL'
        );
    }

    public function down(): void
    {
        DB::statement(
            'ALTER TABLE service_order_items
             DROP CONSTRAINT IF EXISTS service_order_items_service_id_foreign'
        );

        DB::statement(
            'DELETE FROM service_order_items
             WHERE service_id IS NULL'
        );

        DB::statement(
            'ALTER TABLE service_order_items
             ALTER COLUMN service_id SET NOT NULL'
        );

        DB::statement(
            'ALTER TABLE service_order_items
             ADD CONSTRAINT service_order_items_service_id_foreign
             FOREIGN KEY (service_id)
             REFERENCES services(id)
             ON UPDATE CASCADE
             ON DELETE RESTRICT'
        );

        Schema::table('service_order_items', function (Blueprint $table): void {
            $table->dropForeign(['unit_id']);

            $table->dropColumn([
                'service_code',
                'service_description',
                'unit_id',
                'financial_category',
                'economic_result',
                'purpose',
            ]);
        });
    }
};
