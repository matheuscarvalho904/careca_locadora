<?php

declare(strict_types=1);

$projectRoot = $argv[1] ?? dirname(__DIR__);

function patchFile(
    string $path,
    callable $patch,
): void {
    if (! file_exists($path)) {
        throw new RuntimeException("Arquivo não encontrado: {$path}");
    }

    $content = file_get_contents($path);
    $patched = $patch($content);

    if ($patched === $content) {
        echo "[SEM ALTERAÇÃO] {$path}" . PHP_EOL;
        return;
    }

    file_put_contents($path, $patched);
    echo "[CORRIGIDO] {$path}" . PHP_EOL;
}

$deliveryResource = $projectRoot . '/app/Filament/Resources/RentalDeliveries/RentalDeliveryResource.php';
$returnResource = $projectRoot . '/app/Filament/Resources/RentalReturns/RentalReturnResource.php';
$deliveryPage = $projectRoot . '/app/Filament/Resources/RentalDeliveries/Pages/EditRentalDelivery.php';
$returnPage = $projectRoot . '/app/Filament/Resources/RentalReturns/Pages/EditRentalReturn.php';

patchFile($deliveryResource, function (string $content): string {
    if (! str_contains($content, 'ManageDeliveryDamageMap')) {
        $content = str_replace(
            'use App\Filament\Resources\RentalDeliveries\Pages\ListRentalDeliveries;',
            "use App\\Filament\\Resources\\RentalDeliveries\\Pages\\ListRentalDeliveries;\nuse App\\Filament\\Resources\\RentalDeliveries\\Pages\\ManageDeliveryDamageMap;",
            $content
        );
    }

    if (! str_contains($content, "'damage-map'")) {
        $content = str_replace(
            "'edit' => EditRentalDelivery::route('/{record}/edit'),",
            "'edit' => EditRentalDelivery::route('/{record}/edit'),\n            'damage-map' => ManageDeliveryDamageMap::route('/{record}/damage-map'),",
            $content
        );
    }

    return $content;
});

patchFile($returnResource, function (string $content): string {
    if (! str_contains($content, 'ManageReturnDamageMap')) {
        $content = str_replace(
            'use App\Filament\Resources\RentalReturns\Pages\ListRentalReturns;',
            "use App\\Filament\\Resources\\RentalReturns\\Pages\\ListRentalReturns;\nuse App\\Filament\\Resources\\RentalReturns\\Pages\\ManageReturnDamageMap;",
            $content
        );
    }

    if (! str_contains($content, "'damage-map'")) {
        $content = str_replace(
            "'edit' => EditRentalReturn::route('/{record}/edit'),",
            "'edit' => EditRentalReturn::route('/{record}/edit'),\n            'damage-map' => ManageReturnDamageMap::route('/{record}/damage-map'),",
            $content
        );
    }

    return $content;
});

patchFile($deliveryPage, function (string $content): string {
    if (str_contains($content, "Action::make('damage_map')")) {
        return $content;
    }

    return str_replace(
        "return [\n            Action::make('complete')",
        "return [\n            Action::make('damage_map')\n                ->label('Mapa de avarias')\n                ->icon('heroicon-o-map')\n                ->color('warning')\n                ->url(fn (): string => RentalDeliveryResource::getUrl(\n                    'damage-map',\n                    ['record' => \$this->record]\n                )),\n\n            Action::make('complete')",
        $content
    );
});

patchFile($returnPage, function (string $content): string {
    if (str_contains($content, "Action::make('damage_map')")) {
        return $content;
    }

    return str_replace(
        "return [\n            Action::make('recalculate')",
        "return [\n            Action::make('damage_map')\n                ->label('Comparar avarias')\n                ->icon('heroicon-o-map')\n                ->color('warning')\n                ->url(fn (): string => RentalReturnResource::getUrl(\n                    'damage-map',\n                    ['record' => \$this->record]\n                )),\n\n            Action::make('recalculate')",
        $content
    );
});
