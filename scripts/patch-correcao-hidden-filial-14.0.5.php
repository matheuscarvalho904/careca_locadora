<?php

declare(strict_types=1);

$root = $argv[1] ?? dirname(__DIR__);
$resource = $root . '/app/Filament/Resources/Branches/BranchResource.php';

if (! file_exists($resource)) {
    throw new RuntimeException("Arquivo nao encontrado: {$resource}");
}

$content = file_get_contents($resource);
$import = 'use Filament\Forms\Components\Hidden;';

if (! str_contains($content, $import)) {
    $namespace = "namespace App\\Filament\\Resources\\Branches;";

    if (! str_contains($content, $namespace)) {
        throw new RuntimeException(
            'Namespace esperado nao encontrado em BranchResource.php.'
        );
    }

    $content = str_replace(
        $namespace,
        $namespace . PHP_EOL . PHP_EOL . $import,
        $content
    );

    file_put_contents($resource, $content);
    echo "[CORRIGIDO] Import de Hidden adicionado ao BranchResource." . PHP_EOL;
} else {
    echo "[SEM ALTERACAO] Import de Hidden ja existe." . PHP_EOL;
}
