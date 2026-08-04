<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_movements', function (Blueprint $table): void {
            $table->string('source_type', 190)
                ->nullable()
                ->after('financial_receipt_id');

            $table->uuid('source_id')
                ->nullable()
                ->after('source_type');

            $table->index(
                ['organization_id', 'source_type', 'source_id'],
                'cash_movements_org_source_idx'
            );

            $table->unique(
                ['organization_id', 'source_type', 'source_id', 'category'],
                'cash_movements_org_source_category_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('cash_movements', function (Blueprint $table): void {
            $table->dropUnique('cash_movements_org_source_category_unique');
            $table->dropIndex('cash_movements_org_source_idx');
            $table->dropColumn(['source_type', 'source_id']);
        });
    }
};
