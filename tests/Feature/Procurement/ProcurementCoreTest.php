<?php

it('separa produtos em OC e serviços lançados diretamente em OS', function (): void {
    $service = file_get_contents(
        app_path('Services/Procurement/ProcurementValidationService.php')
    );

    expect($service)
        ->toContain('Uma Ordem de Compra aceita somente produtos cadastrados.')
        ->toContain('Informe a descrição do serviço.')
        ->toContain('O serviço deve ser digitado diretamente na Ordem de Serviço.')
        ->toContain('Produtos devem ser lançados em uma Ordem de Compra.')
        ->toContain('Serviços devem ser lançados em uma Ordem de Serviço.')
        ->toContain('Serviços não geram entrada em estoque.')
        ->toContain('Selecione o centro de aplicação.')
        ->toContain('Selecione o ativo de aplicação.')
        ->toContain('Selecione o estoque de destino.');
});

it('mantém origens diretas e originadas nos documentos', function (): void {
    $migration = file_get_contents(
        database_path(
            'migrations/2026_08_02_110000_create_procurement_core_tables.php'
        )
    );

    expect($migration)
        ->toContain("Schema::create('purchase_orders'")
        ->toContain("Schema::create('service_orders'")
        ->toContain("'origin_type'")
        ->toContain("'purchase_request_id'")
        ->toContain("'service_request_id'")
        ->toContain("'quotation_id'");
});
