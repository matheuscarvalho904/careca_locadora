<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table): void {
            if (! Schema::hasColumn('assets', 'old_plate')) {
                $table->string('old_plate', 10)->nullable()->after('plate');
            }

            if (! Schema::hasColumn('assets', 'engine_number')) {
                $table->string('engine_number', 60)->nullable()->after('chassis');
            }

            if (! Schema::hasColumn('assets', 'engine_description')) {
                $table->string('engine_description', 100)->nullable()->after('engine_number');
            }

            if (! Schema::hasColumn('assets', 'engine_displacement_cc')) {
                $table->unsignedInteger('engine_displacement_cc')->nullable()->after('engine_description');
            }

            if (! Schema::hasColumn('assets', 'engine_power_hp')) {
                $table->unsignedSmallInteger('engine_power_hp')->nullable()->after('engine_displacement_cc');
            }

            if (! Schema::hasColumn('assets', 'axles')) {
                $table->unsignedSmallInteger('axles')->nullable()->after('seats');
            }

            if (! Schema::hasColumn('assets', 'gross_weight_t')) {
                $table->decimal('gross_weight_t', 10, 3)->nullable()->after('axles');
            }

            if (! Schema::hasColumn('assets', 'maximum_traction_capacity_t')) {
                $table->decimal('maximum_traction_capacity_t', 10, 3)->nullable()->after('gross_weight_t');
            }

            if (! Schema::hasColumn('assets', 'species')) {
                $table->string('species', 80)->nullable();
            }

            if (! Schema::hasColumn('assets', 'origin')) {
                $table->string('origin', 80)->nullable();
            }

            if (! Schema::hasColumn('assets', 'segment')) {
                $table->string('segment', 80)->nullable();
            }

            if (! Schema::hasColumn('assets', 'subsegment')) {
                $table->string('subsegment', 100)->nullable();
            }

            if (! Schema::hasColumn('assets', 'registration_city')) {
                $table->string('registration_city', 120)->nullable();
            }

            if (! Schema::hasColumn('assets', 'registration_state')) {
                $table->string('registration_state', 2)->nullable();
            }

            if (! Schema::hasColumn('assets', 'fipe_code')) {
                $table->string('fipe_code', 20)->nullable();
            }

            if (! Schema::hasColumn('assets', 'fipe_description')) {
                $table->string('fipe_description', 220)->nullable();
            }

            if (! Schema::hasColumn('assets', 'fipe_value')) {
                $table->decimal('fipe_value', 15, 2)->nullable();
            }

            if (! Schema::hasColumn('assets', 'fipe_reference_month')) {
                $table->string('fipe_reference_month', 80)->nullable();
            }

            if (! Schema::hasColumn('assets', 'fipe_score')) {
                $table->unsignedSmallInteger('fipe_score')->nullable();
            }

            if (! Schema::hasColumn('assets', 'external_situation')) {
                $table->string('external_situation', 120)->nullable();
            }
        });
    }

    public function down(): void
    {
        // Migration aditiva de produção: rollback destrutivo intencionalmente omitido.
    }
};
