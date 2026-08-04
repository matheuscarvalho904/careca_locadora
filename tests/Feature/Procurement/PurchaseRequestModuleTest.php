<?php

it('registra o módulo de solicitação de compra sem referência a obras', function (): void {
    $resource = file_get_contents(
        app_path('Filament/Resources/PurchaseRequests/PurchaseRequestResource.php')
    );

    expect($resource)
        ->toContain("protected static ?string \$navigationLabel = 'Solicitações de compra'")
        ->toContain("Repeater::make('items')")
        ->toContain("'asset' => 'Ativo'")
        ->toContain("'stock' => 'Estoque'")
        ->toContain("'direct_consumption' => 'Consumo interno'")
        ->not->toContain('Obra')
        ->not->toContain('work_id')
        ->not->toContain('project_id');
});

it('mantém estrutura de aprovação configurável', function (): void {
    $migration = file_get_contents(
        database_path('migrations/2026_08_03_010000_create_purchase_requests_and_approvals.php')
    );

    expect($migration)
        ->toContain("Schema::create('approval_flows'")
        ->toContain("Schema::create('approval_flow_steps'")
        ->toContain("Schema::create('approval_instances'")
        ->toContain("Schema::create('approval_instance_steps'");
});
