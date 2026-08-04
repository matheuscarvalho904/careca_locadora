<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$projectRoot = $argv[1] ?? dirname(__DIR__);

require $projectRoot . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$app = require $projectRoot . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';
$app->make(Kernel::class)->bootstrap();

$migration = '2026_08_03_070000_create_visual_damage_map_tables';

$alreadyApplied = DB::table('migrations')
    ->where('migration', $migration)
    ->exists();

if ($alreadyApplied) {
    echo "Migration já aplicada. Nenhuma limpeza necessária." . PHP_EOL;
    exit(0);
}

$tables = [
    'rental_damage_photos',
    'rental_damage_marks',
    'inspection_diagram_views',
    'inspection_diagram_templates',
];

$removed = [];

foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        Schema::drop($table);
        $removed[] = $table;
    }
}

if ($removed === []) {
    echo "Nenhuma tabela parcial encontrada." . PHP_EOL;
    exit(0);
}

echo "Tabelas parciais removidas com segurança:" . PHP_EOL;

foreach ($removed as $table) {
    echo " - {$table}" . PHP_EOL;
}
