<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_lookup_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id')
                ->constrained('organizations')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUuid('asset_id')
                ->nullable()
                ->constrained('assets')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignUuid('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('provider', 50);
            $table->string('plate', 10);
            $table->string('status', 30);
            $table->string('message', 500)->nullable();
            $table->jsonb('response')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestampTz('consulted_at');

            $table->timestampsTz();

            $table->index(['organization_id', 'plate']);
            $table->index(['asset_id', 'consulted_at']);
            $table->index(['provider', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_lookup_logs');
    }
};
