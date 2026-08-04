<?php

declare(strict_types=1);

$root = $argv[1] ?? dirname(__DIR__);

function patchOnce(
    string $path,
    string $pattern,
    string $replacement,
    string $label,
): void {
    if (! file_exists($path)) {
        throw new RuntimeException("Arquivo nao encontrado: {$path}");
    }

    $content = file_get_contents($path);

    if (str_contains($content, $replacement)) {
        echo "[SEM ALTERACAO] {$label}" . PHP_EOL;
        return;
    }

    $patched = preg_replace($pattern, $replacement, $content, 1, $count);

    if ($patched === null) {
        throw new RuntimeException("Regex invalida em {$label}");
    }

    if ($count !== 1) {
        throw new RuntimeException(
            "Nao foi possivel aplicar {$label}. Ocorrencias: {$count}"
        );
    }

    file_put_contents($path, $patched);
    echo "[CORRIGIDO] {$label}" . PHP_EOL;
}

function patchAllQueries(string $path): void
{
    if (! file_exists($path)) {
        throw new RuntimeException("Arquivo nao encontrado: {$path}");
    }

    $content = file_get_contents($path);
    $models = [
        'RentalRatePlan',
        'RentalCommercialItem',
        'RentalCoupon',
        'RentalCommercialRule',
    ];

    foreach ($models as $model) {
        $needle = "{$model}::query()";

        if (! str_contains($content, $needle)) {
            throw new RuntimeException(
                "Consulta {$needle} nao encontrada em {$path}"
            );
        }

        $replacement = "{$model}::query()\n            ->withoutOrganizationScope()";

        if (! str_contains($content, $replacement)) {
            $content = str_replace(
                $needle,
                $replacement,
                $content
            );
        }
    }

    file_put_contents($path, $content);
    echo "[CORRIGIDO] RentalCommercialPricingService" . PHP_EOL;
}

patchOnce(
    $root . '/app/Http/Controllers/Api/PublicCatalogController.php',
    '/Branch::query\(\)\s*\n\s*->where\(/',
    "Branch::query()\n            ->withoutOrganizationScope()\n            ->where(",
    'PublicCatalogController branches'
);

patchOnce(
    $root . '/app/Http/Controllers/Api/PublicCatalogController.php',
    '/AssetCategory::query\(\)\s*\n\s*->where\(/',
    "AssetCategory::query()\n            ->withoutOrganizationScope()\n            ->where(",
    'PublicCatalogController categories'
);

patchOnce(
    $root . '/app/Http/Controllers/Api/PublicVehicleController.php',
    '/Asset::query\(\)\s*\n\s*->with\(/',
    "Asset::query()\n            ->withoutOrganizationScope()\n            ->with(",
    'PublicVehicleController'
);

patchOnce(
    $root . '/app/Domain/Reservations/ReservationAvailabilityEngine.php',
    '/Asset::query\(\)\s*\n\s*->with\(/',
    "Asset::query()\n            ->withoutOrganizationScope()\n            ->with(",
    'ReservationAvailabilityEngine'
);

patchOnce(
    $root . '/app/Domain/Reservations/ReservationConflictEngine.php',
    '/RentalReservationItem::query\(\)\s*\n\s*->with\(/',
    "RentalReservationItem::query()\n            ->withoutOrganizationScope()\n            ->with(",
    'ReservationConflictEngine'
);

patchAllQueries(
    $root . '/app/Services/Rentals/RentalCommercialPricingService.php'
);
