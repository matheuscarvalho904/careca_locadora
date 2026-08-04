<?php

it('registra rotas de PDF para OC e OS', function (): void {
    $routes = file_get_contents(base_path('routes/web.php'));

    expect($routes)
        ->toContain('purchase-orders.pdf')
        ->toContain('service-orders.pdf')
        ->toContain('PurchaseOrderPdfController')
        ->toContain('ServiceOrderPdfController');
});

it('adiciona ações de PDF às telas de edição', function (): void {
    $purchase = file_get_contents(
        app_path(
            'Filament/Resources/PurchaseOrders/Pages/EditPurchaseOrder.php'
        )
    );

    $service = file_get_contents(
        app_path(
            'Filament/Resources/ServiceOrders/Pages/EditServiceOrder.php'
        )
    );

    expect($purchase)
        ->toContain("Action::make('pdf')")
        ->toContain('purchase-orders.pdf')
        ->and($service)
        ->toContain("Action::make('pdf')")
        ->toContain('service-orders.pdf')
        ->toContain('getHeaderActions');
});
