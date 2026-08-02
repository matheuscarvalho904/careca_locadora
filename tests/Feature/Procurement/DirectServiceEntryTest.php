<?php

it('define lançamento direto de serviços na OS', function (): void {
    $migration = file_get_contents(
        database_path(
            'migrations/2026_08_02_120000_enable_direct_service_entry_on_service_orders.php'
        )
    );

    $model = file_get_contents(
        app_path('Models/ServiceOrderItem.php')
    );

    $validation = file_get_contents(
        app_path(
            'Services/Procurement/ProcurementValidationService.php'
        )
    );

    expect($migration)
        ->toContain("'service_description'")
        ->toContain("'service_code'")
        ->toContain("'unit_id'")
        ->toContain('ALTER COLUMN service_id DROP NOT NULL');

    expect($model)
        ->toContain('$item->service_id = null;')
        ->toContain('$item->service_description')
        ->toContain("public function unit(): BelongsTo");

    expect($validation)
        ->toContain('Informe a descrição do serviço.')
        ->toContain('O serviço deve ser digitado diretamente na Ordem de Serviço.')
        ->toContain('Produtos devem ser lançados em uma Ordem de Compra.')
        ->toContain('Serviços não geram entrada em estoque.');
});
