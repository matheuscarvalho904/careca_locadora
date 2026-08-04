<?php

declare(strict_types=1);

$root = $argv[1] ?? dirname(__DIR__);

function writeFile(string $path, string $content): void
{
    file_put_contents($path, $content);
    echo "[CORRIGIDO] {$path}" . PHP_EOL;
}

$servicePage = $root . '/app/Filament/Resources/ServiceOrders/Pages/EditServiceOrder.php';

if (! file_exists($servicePage)) {
    throw new RuntimeException("Arquivo não encontrado: {$servicePage}");
}

$serviceContent = file_get_contents($servicePage);

if (! str_contains($serviceContent, 'use Filament\\Actions\\Action;')) {
    $serviceContent = str_replace(
        'use App\\Filament\\Resources\\ServiceOrders\\ServiceOrderResource;',
        "use App\\Filament\\Resources\\ServiceOrders\\ServiceOrderResource;\nuse Filament\\Actions\\Action;",
        $serviceContent
    );
}

if (! str_contains($serviceContent, "Action::make('pdf')")) {
    $method = <<<'PHP'

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('Visualizar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(fn (): string => route('service-orders.pdf', [
                    'serviceOrder' => $this->record,
                ]))
                ->openUrlInNewTab(),
        ];
    }
PHP;

    $position = strrpos($serviceContent, '}');

    if ($position === false) {
        throw new RuntimeException('Classe EditServiceOrder inválida.');
    }

    $serviceContent = substr($serviceContent, 0, $position)
        . $method
        . PHP_EOL
        . '}';
}

writeFile($servicePage, $serviceContent);

$purchasePage = $root . '/app/Filament/Resources/PurchaseOrders/Pages/EditPurchaseOrder.php';

if (! file_exists($purchasePage)) {
    throw new RuntimeException("Arquivo não encontrado: {$purchasePage}");
}

$purchaseContent = file_get_contents($purchasePage);

if (! str_contains($purchaseContent, "Action::make('pdf')")) {
    if (! str_contains($purchaseContent, 'use Filament\\Actions\\Action;')) {
        $purchaseContent = str_replace(
            'use App\\Filament\\Resources\\PurchaseOrders\\PurchaseOrderResource;',
            "use App\\Filament\\Resources\\PurchaseOrders\\PurchaseOrderResource;\nuse Filament\\Actions\\Action;",
            $purchaseContent
        );
    }

    $purchaseContent = str_replace(
        "return [\n            Action::make('approve')",
        "return [\n            Action::make('pdf')\n                ->label('Visualizar PDF')\n                ->icon('heroicon-o-document-arrow-down')\n                ->color('gray')\n                ->url(fn (): string => route('purchase-orders.pdf', [\n                    'purchaseOrder' => \$this->record,\n                ]))\n                ->openUrlInNewTab(),\n\n            Action::make('approve')",
        $purchaseContent
    );

    writeFile($purchasePage, $purchaseContent);
} else {
    echo "[SEM ALTERAÇÃO] {$purchasePage}" . PHP_EOL;
}
