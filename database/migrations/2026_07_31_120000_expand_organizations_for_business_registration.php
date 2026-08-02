<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table): void {
            $table->string('trade_name', 200)->nullable()->after('legal_name');
            $table->string('person_type', 20)->default('legal')->after('trade_name');
            $table->string('state_registration', 30)->nullable()->after('document');
            $table->string('municipal_registration', 30)->nullable()->after('state_registration');
            $table->string('registration_status', 50)->nullable()->after('municipal_registration');
            $table->string('cnae', 20)->nullable()->after('registration_status');
            $table->date('opened_at')->nullable()->after('cnae');

            $table->string('postal_code', 10)->nullable()->after('whatsapp');
            $table->string('address', 200)->nullable()->after('postal_code');
            $table->string('address_number', 20)->nullable()->after('address');
            $table->string('address_complement', 100)->nullable()->after('address_number');
            $table->string('district', 100)->nullable()->after('address_complement');
            $table->string('city', 100)->nullable()->after('district');
            $table->char('state', 2)->nullable()->after('city');

            $table->string('primary_color', 20)->nullable()->after('favicon_path');
            $table->string('secondary_color', 20)->nullable()->after('primary_color');
            $table->string('domain', 180)->nullable()->after('secondary_color');

            $table->string('company_size', 30)->nullable()->after('domain');
            $table->string('business_segment', 120)->nullable()->after('company_size');
            $table->text('notes')->nullable()->after('business_segment');
            $table->jsonb('tags')->nullable()->after('notes');

            $table->jsonb('external_data')->nullable()->after('metadata');
            $table->timestampTz('external_data_synced_at')->nullable()->after('external_data');

            $table->index(['city', 'state']);
            $table->index('trade_name');
            $table->index('registration_status');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table): void {
            $table->dropIndex(['city', 'state']);
            $table->dropIndex(['trade_name']);
            $table->dropIndex(['registration_status']);

            $table->dropColumn([
                'trade_name',
                'person_type',
                'state_registration',
                'municipal_registration',
                'registration_status',
                'cnae',
                'opened_at',
                'postal_code',
                'address',
                'address_number',
                'address_complement',
                'district',
                'city',
                'state',
                'primary_color',
                'secondary_color',
                'domain',
                'company_size',
                'business_segment',
                'notes',
                'tags',
                'external_data',
                'external_data_synced_at',
            ]);
        });
    }
};
