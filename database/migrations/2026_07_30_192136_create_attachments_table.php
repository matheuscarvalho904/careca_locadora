<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignUuid('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            /*
             * Registro ao qual o arquivo pertence:
             * veículo, contrato, cliente, manutenção, vistoria etc.
             */
            $table->uuidMorphs('attachable');

            $table->string('collection', 100)->default('default');

            $table->string('disk', 50)->default('public');

            $table->string('directory', 500)->nullable();
            $table->string('path', 1000);

            $table->string('original_name', 500);
            $table->string('stored_name', 500);

            $table->string('extension', 20)->nullable();
            $table->string('mime_type', 150)->nullable();

            $table->unsignedBigInteger('size')->default(0);

            $table->string('checksum', 128)->nullable();

            $table->string('visibility', 30)->default('private');

            $table->string('title', 255)->nullable();
            $table->text('description')->nullable();

            $table->unsignedInteger('display_order')->default(0);

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_temporary')->default(false);

            $table->timestampTz('expires_at')->nullable();

            $table->jsonb('metadata')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('organization_id');
            $table->index('uploaded_by');
            $table->index('collection');
            $table->index('mime_type');
            $table->index('checksum');
            $table->index('visibility');
            $table->index('is_temporary');
            $table->index('expires_at');

            $table->index(
                ['organization_id', 'collection'],
                'attachments_org_collection_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};