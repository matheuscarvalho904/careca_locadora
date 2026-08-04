<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->string('postal_code', 8)->nullable()->after('whatsapp');
            $table->string('street', 200)->nullable()->after('postal_code');
            $table->string('address_number', 30)->nullable()->after('street');
            $table->string('address_complement', 100)->nullable()->after('address_number');
            $table->string('district', 150)->nullable()->after('address_complement');
            $table->string('city', 150)->nullable()->after('district');
            $table->string('state', 2)->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->dropColumn([
                'postal_code',
                'street',
                'address_number',
                'address_complement',
                'district',
                'city',
                'state',
            ]);
        });
    }
};
