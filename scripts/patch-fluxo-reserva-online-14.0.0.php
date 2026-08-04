<?php

declare(strict_types=1);

$root = $argv[1] ?? dirname(__DIR__);

function save(string $path, string $content): void
{
    file_put_contents($path, $content);
    echo "[CORRIGIDO] {$path}" . PHP_EOL;
}

$api = $root . '/routes/api.php';
$apiContent = file_get_contents($api);
$apiInclude = "require __DIR__ . '/api-public-reservations.php';";

if (! str_contains($apiContent, $apiInclude)) {
    save(
        $api,
        rtrim($apiContent) . PHP_EOL . PHP_EOL . $apiInclude . PHP_EOL
    );
}

$web = $root . '/routes/web.php';
$webContent = file_get_contents($web);

if (! str_contains($webContent, "public/vehicle-show")) {
    $route = <<<'PHP'

Route::get('/veiculos/{asset}', function (string $asset) {
    return \Inertia\Inertia::render('public/vehicle-show', [
        'assetId' => $asset,
    ]);
})->name('public.vehicles.show');
PHP;

    save($web, rtrim($webContent) . PHP_EOL . $route . PHP_EOL);
}

$app = $root . '/resources/js/app.tsx';
$appContent = file_get_contents($app);

$appContent = str_replace(
    "case name === 'welcome':\n                return null;",
    "case name === 'welcome':\n            case name.startsWith('public/'):\n                return null;",
    $appContent
);

save($app, $appContent);

$welcome = $root . '/resources/js/pages/welcome.tsx';
$welcomeContent = file_get_contents($welcome);

$welcomeContent = str_replace(
    '<button\n                                                                type="button"\n                                                                className="rounded-xl bg-[#d71920] px-4 py-3 text-xs font-black text-white transition hover:bg-red-700"\n                                                            >\n                                                                Reservar\n                                                            </button>',
    '<a\n                                                                href={`/veiculos/${vehicle.id}?starts_at=${encodeURIComponent(filters.starts_at)}&ends_at=${encodeURIComponent(filters.ends_at)}&branch_id=${encodeURIComponent(vehicle.branch?.id ?? filters.branch_id)}&category_id=${encodeURIComponent(vehicle.category?.id ?? filters.category_id)}`}\n                                                                className="rounded-xl bg-[#d71920] px-4 py-3 text-xs font-black text-white transition hover:bg-red-700"\n                                                            >\n                                                                Reservar\n                                                            </a>',
    $welcomeContent
);

save($welcome, $welcomeContent);
