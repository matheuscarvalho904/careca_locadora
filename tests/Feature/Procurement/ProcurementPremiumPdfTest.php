<?php

it('possui controllers de PDF para OC e OS', function (): void {
    expect(file_exists(
        app_path('Http/Controllers/Procurement/PurchaseOrderPdfController.php')
    ))->toBeTrue()
        ->and(file_exists(
            app_path('Http/Controllers/Procurement/ServiceOrderPdfController.php')
        ))->toBeTrue();
});

it('possui serviço documental unificado', function (): void {
    $service = file_get_contents(
        app_path('Services/Documents/ProcurementDocumentService.php')
    );

    expect($service)
        ->toContain('purchaseOrderData')
        ->toContain('serviceOrderData')
        ->toContain('resolveCompanyAndBranch')
        ->toContain("'sha256'")
        ->toContain('documentHash');
});

it('possui layout premium compartilhado', function (): void {
    $view = file_get_contents(
        resource_path('views/pdf/procurement-order-premium.blade.php')
    );

    expect($view)
        ->toContain('$documentTitle')
        ->toContain('Dados do documento')
        ->toContain("'Produtos e aplicações'")
        ->toContain("'Serviços e aplicações'")
        ->toContain('Resumo financeiro')
        ->toContain('Autorizações e assinaturas')
        ->toContain('Hash SHA-256');
});

it('mantém aplicação por ativo, estoque e centro de aplicação', function (): void {
    $view = file_get_contents(
        resource_path('views/pdf/procurement-order-premium.blade.php')
    );

    expect($view)
        ->toContain("'asset' =>")
        ->toContain("'stock' =>")
        ->toContain("'application_center' =>")
        ->toContain('meter_reading')
        ->toContain('costCenter');
});
