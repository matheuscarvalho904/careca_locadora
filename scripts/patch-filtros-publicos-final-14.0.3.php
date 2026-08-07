<?php

declare(strict_types=1);

$root = $argv[1] ?? dirname(__DIR__);

$targets = [
    'app/Http/Controllers/Api/PublicCatalogController.php',
    'app/Http/Controllers/Api/PublicVehicleController.php',
    'app/Domain/Reservations/ReservationAvailabilityEngine.php',
    'app/Domain/Reservations/ReservationConflictEngine.php',
    'app/Services/Rentals/RentalCommercialPricingService.php',
];

foreach ($targets as $relative) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);

    if (! file_exists($path)) {
        throw new RuntimeException("Arquivo nao encontrado: {$path}");
    }

    $content = file_get_contents($path);
    $patched = str_replace(
        '->withoutOrganizationScope()',
        '->withoutGlobalScopes()',
        $content,
        $count
    );

    if ($count > 0) {
        file_put_contents($path, $patched);
        echo "[CORRIGIDO] {$relative} ({$count} ocorrencia(s))" . PHP_EOL;
    } else {
        echo "[SEM ALTERACAO] {$relative}" . PHP_EOL;
    }
}
