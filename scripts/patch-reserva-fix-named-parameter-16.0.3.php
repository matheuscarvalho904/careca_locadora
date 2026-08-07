<?php

declare(strict_types=1);

$root = $argv[1] ?? dirname(__DIR__);
$app = $root . '/app';

if (! is_dir($app)) {
    throw new RuntimeException("Diretorio app nao encontrado: {$app}");
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(
        $app,
        FilesystemIterator::SKIP_DOTS
    )
);

$found = 0;
$changed = 0;

foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $content = file_get_contents($path);

    if (! str_contains($content, 'selectedItemIds:')) {
        continue;
    }

    $found++;
    $relative = str_replace(
        [$root . DIRECTORY_SEPARATOR, '\\'],
        ['', '/'],
        $path
    );

    echo "[ENCONTRADO] {$relative}" . PHP_EOL;

    $patched = str_replace(
        'selectedItemIds:',
        'itemIds:',
        $content,
        $count
    );

    if ($count > 0) {
        file_put_contents($path, $patched);
        $changed += $count;
        echo "[CORRIGIDO] {$relative} ({$count} ocorrencia(s))" . PHP_EOL;
    }
}

if ($found === 0) {
    throw new RuntimeException(
        'Nenhuma ocorrencia de selectedItemIds: foi encontrada. '
        . 'O projeto pode ter sido alterado desde o erro.'
    );
}

echo "[OK] Total de substituicoes: {$changed}" . PHP_EOL;
