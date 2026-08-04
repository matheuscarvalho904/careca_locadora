<?php

declare(strict_types=1);

$root = $argv[1] ?? dirname(__DIR__);

function patchFile(
    string $path,
    array $replacements,
): void {
    if (! file_exists($path)) {
        throw new RuntimeException("Arquivo nao encontrado: {$path}");
    }

    $content = file_get_contents($path);
    $original = $content;

    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }

    if ($content === $original) {
        echo "[SEM ALTERACAO] {$path}" . PHP_EOL;
        return;
    }

    file_put_contents($path, $content);
    echo "[CORRIGIDO] {$path}" . PHP_EOL;
}

patchFile(
    $root . '/app/Http/Controllers/Api/PublicCatalogController.php',
    [
        "Branch::query()\n            ->where('organization_id'," =>
            "Branch::query()\n            ->withoutOrganizationScope()\n            ->where('organization_id',",

        "AssetCategory::query()\n            ->where('organization_id'," =>
            "AssetCategory::query()\n            ->withoutOrganizationScope()\n            ->where('organization_id',",
    ]
);

patchFile(
    $root . '/app/Http/Controllers/Api/PublicVehicleController.php',
    [
        "Asset::query()\n            ->with([" =>
            "Asset::query()\n            ->withoutOrganizationScope()\n            ->with([",
    ]
);

patchFile(
    $root . '/app/Domain/Reservations/ReservationAvailabilityEngine.php',
    [
        "return Asset::query()\n            ->with([" =>
            "return Asset::query()\n            ->withoutOrganizationScope()\n            ->with([",
    ]
);

patchFile(
    $root . '/app/Domain/Reservations/ReservationConflictEngine.php',
    [
        "return RentalReservationItem::query()\n            ->with([" =>
            "return RentalReservationItem::query()\n            ->withoutOrganizationScope()\n            ->with([",
    ]
);

patchFile(
    $root . '/app/Services/Rentals/RentalCommercialPricingService.php',
    [
        "return RentalRatePlan::query()\n            ->where(" =>
            "return RentalRatePlan::query()\n            ->withoutOrganizationScope()\n            ->where(",

        "return RentalCommercialItem::query()\n            ->where(" =>
            "return RentalCommercialItem::query()\n            ->withoutOrganizationScope()\n            ->where(",

        "$coupon = RentalCoupon::query()\n            ->where(" =>
            "$coupon = RentalCoupon::query()\n            ->withoutOrganizationScope()\n            ->where(",

        "return RentalCommercialRule::query()\n            ->where(" =>
            "return RentalCommercialRule::query()\n            ->withoutOrganizationScope()\n            ->where(",
    ]
);
