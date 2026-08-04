<?php

declare(strict_types=1);

$root = $argv[1] ?? dirname(__DIR__);

function patch(string $path, callable $callback): void
{
    if (! file_exists($path)) {
        throw new RuntimeException("Arquivo não encontrado: {$path}");
    }

    $content = file_get_contents($path);
    $updated = $callback($content);

    if ($updated !== $content) {
        file_put_contents($path, $updated);
        echo "[CORRIGIDO] {$path}" . PHP_EOL;
    } else {
        echo "[SEM ALTERAÇÃO] {$path}" . PHP_EOL;
    }
}

patch($root . '/routes/web.php', function (string $content): string {
    if (str_contains($content, 'purchase-orders.pdf')) {
        return $content;
    }

    return rtrim($content) . <<<'PHP'


Route::middleware('auth')->group(function (): void {
    Route::get(
        '/app/purchase-orders/{purchaseOrder}/pdf',
        \App\Http\Controllers\Procurement\PurchaseOrderPdfController::class
    )->name('purchase-orders.pdf');

    Route::get(
        '/app/service-orders/{serviceOrder}/pdf',
        \App\Http\Controllers\Procurement\ServiceOrderPdfController::class
    )->name('service-orders.pdf');
});

PHP;
});

patch(
    $root . '/app/Filament/Resources/PurchaseOrders/Pages/EditPurchaseOrder.php',
    function (string $content): string {
        if (str_contains($content, "Action::make('pdf')")) {
            return $content;
        }

        return str_replace(
            "return [\n            Action::make('approve')",
            "return [\n            Action::make('pdf')\n                ->label('Visualizar PDF')\n                ->icon('heroicon-o-document-arrow-down')\n                ->color('gray')\n                ->url(fn (): string => route('purchase-orders.pdf', [\n                    'purchaseOrder' => \$this->record,\n                ]))\n                ->openUrlInNewTab(),\n\n            Action::make('approve')",
            $content
        );
    }
);

patch(
    $root . '/app/Filament/Resources/ServiceOrders/Pages/EditServiceOrder.php',
    function (string $content): string {
        if (str_contains($content, "Action::make('pdf')")) {
            return $content;
        }

        return str_replace(
            "return [",
            "return [\n            Action::make('pdf')\n                ->label('Visualizar PDF')\n                ->icon('heroicon-o-document-arrow-down')\n                ->color('gray')\n                ->url(fn (): string => route('service-orders.pdf', [\n                    'serviceOrder' => \$this->record,\n                ]))\n                ->openUrlInNewTab(),",
            $content
        );
    }
);
