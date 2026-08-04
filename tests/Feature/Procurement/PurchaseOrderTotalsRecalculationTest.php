<?php

it('recalcula os totais após criar os itens da OC', function (): void {
    $service = file_get_contents(
        app_path('Services/Procurement/QuotationToPurchaseOrderService.php')
    );

    expect($service)
        ->toContain('PurchaseOrderTotalsService::class')
        ->toContain('->recalculate($order)');
});

it('mantém os totais sincronizados ao editar ou excluir item', function (): void {
    $model = file_get_contents(
        app_path('Models/PurchaseOrderItem.php')
    );

    expect($model)
        ->toContain('static::saved(function (self $item)')
        ->toContain('static::deleted(function (self $item)')
        ->toContain('PurchaseOrderTotalsService::class');
});

it('fornece comando para corrigir OCs existentes', function (): void {
    $command = file_get_contents(
        app_path('Console/Commands/RecalculatePurchaseOrderTotals.php')
    );

    expect($command)
        ->toContain('procurement:recalculate-purchase-orders')
        ->toContain("where('number', \$this->option('number'))")
        ->toContain('Ordem(ns) de Compra recalculada(s)');
});
