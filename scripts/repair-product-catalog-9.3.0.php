<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$migration = '2026_08_02_160000_expand_products_and_create_catalog_tables';

$alreadyRan = DB::table('migrations')
    ->where('migration', $migration)
    ->exists();

if ($alreadyRan) {
    fwrite(STDOUT, "Migration já registrada; nenhuma limpeza necessária.\n");
    exit(0);
}

if (Schema::hasTable('product_brands')) {
    Schema::drop('product_brands');
    fwrite(STDOUT, "Tabela órfã product_brands removida.\n");
}

if (Schema::hasTable('product_categories')) {
    Schema::drop('product_categories');
    fwrite(STDOUT, "Tabela órfã product_categories removida.\n");
}

fwrite(STDOUT, "Pré-reparo do catálogo concluído.\n");
