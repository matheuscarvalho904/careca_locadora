<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
             * Proprietário da configuração:
             * Organization, Company, Branch ou outro escopo futuro.
             */
            $table->uuidMorphs('owner');

            $table->string('group', 100)->default('general');
            $table->string('key', 150);

            $table->text('value')->nullable();

            $table->string('value_type', 30)->default('string');

            $table->boolean('is_public')->default(false);
            $table->boolean('is_encrypted')->default(false);
            $table->boolean('is_editable')->default(true);

            $table->text('description')->nullable();

            $table->jsonb('metadata')->nullable();

            $table->timestampsTz();

            $table->unique(
                [
                    'organization_id',
                    'owner_type',
                    'owner_id',
                    'group',
                    'key',
                ],
                'system_settings_scope_group_key_unique'
            );

            $table->index('organization_id');
            $table->index('group');
            $table->index('key');
            $table->index('value_type');

            $table->index(
                ['organization_id', 'group'],
                'system_settings_org_group_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};