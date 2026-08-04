<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('accounts_payable', function (Blueprint $table): void {
   $table->foreignUuid('purchase_order_id')->nullable()->after('asset_id')->constrained('purchase_orders')->nullOnDelete();
   $table->foreignUuid('purchase_receipt_id')->nullable()->after('purchase_order_id')->constrained('purchase_receipts')->nullOnDelete();
   $table->unsignedSmallInteger('installment_number')->nullable()->after('purchase_receipt_id');
   $table->unsignedSmallInteger('installment_count')->nullable()->after('installment_number');
   $table->string('origin_type',50)->nullable()->after('installment_count');
   $table->unique(['purchase_receipt_id','installment_number'],'accounts_payable_receipt_installment_unique');
   $table->index(['organization_id','purchase_order_id'],'accounts_payable_org_purchase_order_idx');
  });
 }
 public function down(): void {
  Schema::table('accounts_payable', function (Blueprint $table): void {
   $table->dropUnique('accounts_payable_receipt_installment_unique');
   $table->dropIndex('accounts_payable_org_purchase_order_idx');
   $table->dropConstrainedForeignId('purchase_receipt_id');
   $table->dropConstrainedForeignId('purchase_order_id');
   $table->dropColumn(['installment_number','installment_count','origin_type']);
  });
 }
};
