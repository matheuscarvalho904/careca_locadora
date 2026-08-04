<?php

declare(strict_types=1);

$root = $argv[1] ?? dirname(__DIR__);

function patch(string $path, callable $callback): void
{
    if (! file_exists($path)) {
        throw new RuntimeException("Arquivo não encontrado: {$path}");
    }

    $content = file_get_contents($path);
    $new = $callback($content);

    if ($new !== $content) {
        file_put_contents($path, $new);
        echo "[CORRIGIDO] {$path}" . PHP_EOL;
    } else {
        echo "[SEM ALTERAÇÃO] {$path}" . PHP_EOL;
    }
}

$deliveryResource = $root . '/app/Filament/Resources/RentalDeliveries/RentalDeliveryResource.php';
$returnResource = $root . '/app/Filament/Resources/RentalReturns/RentalReturnResource.php';
$deliveryEdit = $root . '/app/Filament/Resources/RentalDeliveries/Pages/EditRentalDelivery.php';
$returnEdit = $root . '/app/Filament/Resources/RentalReturns/Pages/EditRentalReturn.php';
$routes = $root . '/routes/web.php';

patch($deliveryResource, function (string $content): string {
    if (! str_contains($content, 'ManageDeliveryChecklistPremium')) {
        $content = str_replace(
            'use App\Filament\Resources\RentalDeliveries\Pages\ManageDeliveryDamageMap;',
            "use App\\Filament\\Resources\\RentalDeliveries\\Pages\\ManageDeliveryDamageMap;\nuse App\\Filament\\Resources\\RentalDeliveries\\Pages\\ManageDeliveryChecklistPremium;",
            $content
        );
    }

    if (! str_contains($content, "'checklist-premium'")) {
        $content = str_replace(
            "'damage-map' => ManageDeliveryDamageMap::route('/{record}/damage-map'),",
            "'damage-map' => ManageDeliveryDamageMap::route('/{record}/damage-map'),\n            'checklist-premium' => ManageDeliveryChecklistPremium::route('/{record}/checklist-premium'),",
            $content
        );
    }

    return $content;
});

patch($returnResource, function (string $content): string {
    if (! str_contains($content, 'ManageReturnChecklistPremium')) {
        $content = str_replace(
            'use App\Filament\Resources\RentalReturns\Pages\ManageReturnDamageMap;',
            "use App\\Filament\\Resources\\RentalReturns\\Pages\\ManageReturnDamageMap;\nuse App\\Filament\\Resources\\RentalReturns\\Pages\\ManageReturnChecklistPremium;",
            $content
        );
    }

    if (! str_contains($content, "'checklist-premium'")) {
        $content = str_replace(
            "'damage-map' => ManageReturnDamageMap::route('/{record}/damage-map'),",
            "'damage-map' => ManageReturnDamageMap::route('/{record}/damage-map'),\n            'checklist-premium' => ManageReturnChecklistPremium::route('/{record}/checklist-premium'),",
            $content
        );
    }

    return $content;
});

patch($deliveryEdit, function (string $content): string {
    if (str_contains($content, "Action::make('checklist_premium')")) {
        return $content;
    }

    return str_replace(
        "return [\n            Action::make('damage_map')",
        "return [\n            Action::make('checklist_premium')\n                ->label('Checklist Premium')\n                ->icon('heroicon-o-document-check')\n                ->color('success')\n                ->url(fn (): string => RentalDeliveryResource::getUrl(\n                    'checklist-premium',\n                    ['record' => \$this->record]\n                )),\n\n            Action::make('damage_map')",
        $content
    );
});

patch($returnEdit, function (string $content): string {
    if (str_contains($content, "Action::make('checklist_premium')")) {
        return $content;
    }

    return str_replace(
        "return [\n            Action::make('damage_map')",
        "return [\n            Action::make('checklist_premium')\n                ->label('Checklist Premium')\n                ->icon('heroicon-o-document-check')\n                ->color('success')\n                ->url(fn (): string => RentalReturnResource::getUrl(\n                    'checklist-premium',\n                    ['record' => \$this->record]\n                )),\n\n            Action::make('damage_map')",
        $content
    );
});

patch($routes, function (string $content): string {
    if (str_contains($content, "rental-deliveries.checklist-pdf")) {
        return $content;
    }

    $block = <<<'PHP'

Route::middleware('auth')->group(function (): void {
    Route::get(
        '/app/rental-deliveries/{delivery}/checklist-pdf',
        \App\Http\Controllers\Rentals\RentalDeliveryChecklistPdfController::class
    )->name('rental-deliveries.checklist-pdf');

    Route::get(
        '/app/rental-returns/{rentalReturn}/checklist-pdf',
        \App\Http\Controllers\Rentals\RentalReturnChecklistPdfController::class
    )->name('rental-returns.checklist-pdf');
});

PHP;

    return rtrim($content) . PHP_EOL . PHP_EOL . $block;
});
