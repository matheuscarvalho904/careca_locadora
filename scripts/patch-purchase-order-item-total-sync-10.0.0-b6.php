<?php

$file = dirname(__DIR__) . '/app/Models/PurchaseOrderItem.php';
$content = file_get_contents($file);

if (! str_contains($content, 'PurchaseOrderTotalsService::class')) {
    if (! str_contains($content, 'use App\Services\Procurement\PurchaseOrderTotalsService;')) {
        $content = str_replace(
            'use App\Services\Procurement\ProcurementValidationService;',
            "use App\\Services\\Procurement\\ProcurementValidationService;\nuse App\\Services\\Procurement\\PurchaseOrderTotalsService;",
            $content
        );
    }

    $needle = <<<'PHP'
        static::saving(function (self $item): void {
            app(ProcurementValidationService::class)->validatePurchaseItem(
                $item->toArray()
            );

            $item->total_value = max(
                0,
                ((float) $item->quantity * (float) $item->unit_value)
                - (float) $item->discount_value
            );
        });
PHP;

    $replacement = $needle . <<<'PHP'

        static::saved(function (self $item): void {
            if ($item->order) {
                app(PurchaseOrderTotalsService::class)->recalculate($item->order);
            }
        });

        static::deleted(function (self $item): void {
            if ($item->order) {
                app(PurchaseOrderTotalsService::class)->recalculate($item->order);
            }
        });
PHP;

    if (! str_contains($content, $needle)) {
        fwrite(STDERR, "Bloco saving não encontrado em PurchaseOrderItem.php.\n");
        exit(1);
    }

    $content = str_replace($needle, $replacement, $content);
}

file_put_contents($file, $content);
