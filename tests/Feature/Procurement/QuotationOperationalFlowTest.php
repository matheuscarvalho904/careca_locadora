<?php

it('inclui tela para lançamento das propostas dos fornecedores', function (): void {
    $page = file_get_contents(
        app_path('Filament/Pages/QuotationProposalEntry.php')
    );

    $view = file_get_contents(
        resource_path('views/filament/pages/quotation-proposal-entry.blade.php')
    );

    expect($page)
        ->toContain('function saveProposal()')
        ->toContain('QuotationSupplierItem::query()->updateOrCreate')
        ->and($view)
        ->toContain('Valores por produto')
        ->toContain('Salvar proposta');
});

it('permite selecionar vencedores e gerar ordens de compra', function (): void {
    $page = file_get_contents(
        app_path('Filament/Pages/QuotationComparison.php')
    );

    $view = file_get_contents(
        resource_path('views/filament/pages/quotation-comparison.blade.php')
    );

    expect($page)
        ->toContain('function selectWinner(')
        ->toContain('function generateOrders()')
        ->toContain('QuotationToPurchaseOrderService')
        ->and($view)
        ->toContain('Gerar Ordem(ns) de Compra')
        ->toContain('Menor valor')
        ->toContain('Proposta não lançada');
});
