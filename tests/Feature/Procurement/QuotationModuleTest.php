<?php

it('registra o módulo de cotações e o mapa comparativo', function (): void {
    expect(file_exists(app_path('Filament/Resources/Quotations/QuotationResource.php')))
        ->toBeTrue()
        ->and(file_exists(app_path('Filament/Pages/QuotationComparison.php')))
        ->toBeTrue()
        ->and(file_exists(resource_path('views/filament/pages/quotation-comparison.blade.php')))
        ->toBeTrue();
});

it('mantém rastreabilidade SC cotação OC sem obras', function (): void {
    $migration = file_get_contents(
        database_path('migrations/2026_08_03_020000_create_quotations_and_link_purchase_orders.php')
    );

    $service = file_get_contents(
        app_path('Services/Procurement/QuotationToPurchaseOrderService.php')
    );

    expect($migration)
        ->toContain("Schema::create('quotations'")
        ->toContain("Schema::create('quotation_suppliers'")
        ->toContain("Schema::create('quotation_items'")
        ->toContain("Schema::create('quotation_supplier_items'")
        ->and($service)
        ->toContain("'purchase_request_id' => \$quotation->purchase_request_id")
        ->toContain("'quotation_id' => \$quotation->id")
        ->not->toContain('work_id')
        ->not->toContain('project_id');
});
