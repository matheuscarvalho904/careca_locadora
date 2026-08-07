<?php
declare(strict_types=1);

$root = $argv[1] ?? dirname(__DIR__);
$controller = $root . '/app/Http/Controllers/Api/PublicCatalogController.php';

if (! file_exists($controller)) {
    throw new RuntimeException("Arquivo nao encontrado: {$controller}");
}

$content = file_get_contents($controller);
$old = "->map(function (Collection \$group): array {";
$new = "->map(function (Collection \$group) use (\$search): array {";

if (str_contains($content, $new)) {
    echo "[SEM ALTERACAO] Closure ja corrigida." . PHP_EOL;
    exit(0);
}

if (! str_contains($content, $old)) {
    throw new RuntimeException('Closure do catalogo nao localizada.');
}

file_put_contents($controller, str_replace($old, $new, $content, $count));

if ($count !== 1) {
    throw new RuntimeException("Substituicoes inesperadas: {$count}");
}

echo "[CORRIGIDO] \$search capturado na closure do catalogo." . PHP_EOL;
