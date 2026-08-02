<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_movements', function (Blueprint $table): void {
            $table->string('number', 40)->nullable()->after('id');
            $table->string('category', 50)->nullable()->after('type');
            $table->uuid('transfer_group_id')->nullable()->after('category');
            $table->string('reconciliation_status', 30)->default('pending')->after('status');
            $table->timestampTz('reconciled_at')->nullable()->after('occurred_at');
            $table->foreignUuid('reconciled_by')->nullable()->after('reconciled_at')
                ->constrained('users')->cascadeOnUpdate()->nullOnDelete();

            $table->unique(['organization_id', 'number'], 'cash_movements_org_number_unique');
            $table->index(['organization_id', 'reconciliation_status', 'occurred_at'], 'cash_movements_org_reconciliation_date_idx');
            $table->index(['organization_id', 'transfer_group_id'], 'cash_movements_org_transfer_group_idx');
        });
    }

    public function down(): void
    {
        Schema::table('cash_movements', function (Blueprint $table): void {
            $table->dropForeign(['reconciled_by']);
            $table->dropUnique('cash_movements_org_number_unique');
            $table->dropIndex('cash_movements_org_reconciliation_date_idx');
            $table->dropIndex('cash_movements_org_transfer_group_idx');
            $table->dropColumn(['number','category','transfer_group_id','reconciliation_status','reconciled_at','reconciled_by']);
        });
    }
};
