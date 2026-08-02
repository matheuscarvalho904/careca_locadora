<?php

it('registra as telas principais de compras e serviços', function (): void {
    $resources = [
        app_path('Filament/Resources/Units/UnitResource.php'),
        app_path('Filament/Resources/Products/ProductResource.php'),
        app_path('Filament/Resources/Warehouses/WarehouseResource.php'),
        app_path('Filament/Resources/ApplicationCenters/ApplicationCenterResource.php'),
        app_path('Filament/Resources/PurchaseOrders/PurchaseOrderResource.php'),
        app_path('Filament/Resources/ServiceOrders/ServiceOrderResource.php'),
    ];

    foreach ($resources as $resource) {
        expect(file_exists($resource))->toBeTrue();
    }

    $purchase = file_get_contents(
        app_path('Filament/Resources/PurchaseOrders/PurchaseOrderResource.php')
    );

    $service = file_get_contents(
        app_path('Filament/Resources/ServiceOrders/ServiceOrderResource.php')
    );

    expect($purchase)
        ->toContain("->label('Produto')")
        ->toContain("'stock' => 'Estoque'")
        ->toContain("'direct' => 'Compra direta'")
        ->not->toContain("service_description");

    expect($service)
        ->toContain("->label('Descrição do serviço')")
        ->toContain("'direct' => 'Serviço direto'")
        ->toContain("->addActionLabel('Adicionar serviço')")
        ->not->toContain("product_id");
});
