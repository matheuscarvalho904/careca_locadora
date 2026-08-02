<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_partner_documents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_partner_id')
                ->constrained('business_partners')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('title', 150)->nullable();
            $table->string('number', 80)->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('file_path', 500);
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->index('business_partner_id');
            $table->index(['business_partner_id', 'type']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_partner_documents');
    }
};
