<?php

declare(strict_types=1);

$root = $argv[1] ?? dirname(__DIR__);

function save(string $path, string $content): void
{
    file_put_contents($path, $content);
    echo "[CORRIGIDO] {$path}" . PHP_EOL;
}

$bootstrap = $root . '/bootstrap/app.php';

if (! file_exists($bootstrap)) {
    throw new RuntimeException("Arquivo não encontrado: {$bootstrap}");
}

$content = file_get_contents($bootstrap);

if (str_contains($content, "api: __DIR__.'/../routes/api.php'")) {
    echo "[SEM ALTERACAO] API ja registrada no bootstrap." . PHP_EOL;
} elseif (str_contains($content, "api: __DIR__ . '/../routes/api.php'")) {
    echo "[SEM ALTERACAO] API ja registrada no bootstrap." . PHP_EOL;
} else {
    $patterns = [
        "web: __DIR__.'/../routes/web.php',",
        "web: __DIR__ . '/../routes/web.php',",
    ];

    $patched = false;

    foreach ($patterns as $pattern) {
        if (! str_contains($content, $pattern)) {
            continue;
        }

        $replacement = $pattern . PHP_EOL
            . "        api: __DIR__.'/../routes/api.php',";

        $content = str_replace($pattern, $replacement, $content);
        $patched = true;
        break;
    }

    if (! $patched) {
        throw new RuntimeException(
            'Nao foi possivel localizar withRouting() no bootstrap/app.php.'
        );
    }

    save($bootstrap, $content);
}

$api = $root . '/routes/api.php';

if (! file_exists($api)) {
    save(
        $api,
        "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n"
    );
}

$apiContent = file_get_contents($api);
$include = "require __DIR__ . '/api-public-catalog.php';";

if (! str_contains($apiContent, $include)) {
    $apiContent = rtrim($apiContent) . PHP_EOL . PHP_EOL
        . $include . PHP_EOL;

    save($api, $apiContent);
} else {
    echo "[SEM ALTERACAO] {$api}" . PHP_EOL;
}
