<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table): void {
            /*
             * BIGINT é adequado para uma tabela técnica de alto volume.
             */
            $table->id();

            $table->foreignUuid('organization_id')
                ->nullable()
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('log_name')->nullable()->index();
            $table->text('description');

            $table->nullableUuidMorphs('subject', 'subject');

            $table->string('event')->nullable();

            $table->nullableUuidMorphs('causer', 'causer');

            $table->jsonb('attribute_changes')->nullable();
            $table->jsonb('properties')->nullable();

            $table->timestampsTz();

            $table->index(
                ['organization_id', 'created_at'],
                'activity_log_organization_created_at_index'
            );

            $table->index(
                ['organization_id', 'log_name'],
                'activity_log_organization_log_name_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};