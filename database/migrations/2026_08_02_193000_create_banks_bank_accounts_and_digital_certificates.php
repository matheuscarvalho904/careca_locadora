<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('banks',function(Blueprint $t):void{$t->uuid('id')->primary();$t->string('code',10)->unique();$t->string('ispb',20)->nullable();$t->string('name',180);$t->string('short_name',100)->nullable();$t->string('status',30)->default('active');$t->timestampsTz();});
  Schema::create('bank_accounts',function(Blueprint $t):void{
   $t->uuid('id')->primary();$t->foreignUuid('organization_id')->constrained('organizations')->cascadeOnUpdate()->restrictOnDelete();$t->foreignUuid('bank_id')->constrained('banks')->cascadeOnUpdate()->restrictOnDelete();
   $t->foreignUuid('company_id')->nullable()->constrained('companies')->cascadeOnUpdate()->cascadeOnDelete();$t->foreignUuid('branch_id')->nullable()->constrained('branches')->cascadeOnUpdate()->cascadeOnDelete();$t->foreignUuid('business_partner_id')->nullable()->constrained('business_partners')->cascadeOnUpdate()->cascadeOnDelete();
   $t->string('owner_type',30);$t->string('description',120)->nullable();$t->string('agency',30)->nullable();$t->string('agency_digit',10)->nullable();$t->string('account_number',40)->nullable();$t->string('account_digit',10)->nullable();$t->string('account_type',30)->default('checking');$t->string('holder_name',180);$t->string('holder_document',20)->nullable();$t->string('pix_key_type',30)->nullable();$t->text('pix_key')->nullable();
   $t->boolean('is_primary')->default(false);$t->boolean('use_for_payments')->default(false);$t->boolean('use_for_receipts')->default(false);$t->boolean('use_for_invoices')->default(false);$t->boolean('use_for_boleto')->default(false);$t->string('status',30)->default('active');$t->text('notes')->nullable();$t->jsonb('metadata')->nullable();$t->timestampsTz();$t->softDeletesTz();
  });
  Schema::create('digital_certificates',function(Blueprint $t):void{
   $t->uuid('id')->primary();$t->foreignUuid('organization_id')->constrained('organizations')->cascadeOnUpdate()->restrictOnDelete();$t->foreignUuid('company_id')->constrained('companies')->cascadeOnUpdate()->cascadeOnDelete();$t->string('name',150);$t->string('certificate_type',10);$t->string('environment',20)->default('production');$t->jsonb('purposes')->nullable();$t->string('file_path',500)->nullable();$t->text('password_encrypted')->nullable();$t->string('serial_number',180)->nullable();$t->string('subject_name',255)->nullable();$t->string('subject_document',20)->nullable();$t->string('issuer_name',255)->nullable();$t->date('issued_at')->nullable();$t->date('expires_at')->nullable();$t->unsignedSmallInteger('alert_days_before')->default(30);$t->boolean('is_primary')->default(false);$t->string('status',30)->default('active');$t->text('notes')->nullable();$t->jsonb('metadata')->nullable();$t->timestampsTz();$t->softDeletesTz();
  });
  Schema::table('accounts_payable',function(Blueprint $t):void{$t->foreignUuid('bank_account_id')->nullable()->after('supplier_id')->constrained('bank_accounts')->cascadeOnUpdate()->nullOnDelete();$t->jsonb('bank_snapshot')->nullable()->after('bank_account_id');});
  Schema::table('rental_invoices',function(Blueprint $t):void{$t->foreignUuid('bank_account_id')->nullable()->after('cost_center_id')->constrained('bank_accounts')->cascadeOnUpdate()->nullOnDelete();$t->jsonb('bank_snapshot')->nullable()->after('bank_account_id');});
 }
 public function down(): void {Schema::table('rental_invoices',function(Blueprint $t):void{$t->dropConstrainedForeignId('bank_account_id');$t->dropColumn('bank_snapshot');});Schema::table('accounts_payable',function(Blueprint $t):void{$t->dropConstrainedForeignId('bank_account_id');$t->dropColumn('bank_snapshot');});Schema::dropIfExists('digital_certificates');Schema::dropIfExists('bank_accounts');Schema::dropIfExists('banks');}
};
