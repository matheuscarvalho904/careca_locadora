<?php

it('exige leitura somente para itens de ativo na geração da OC', function (): void {
    $service = file_get_contents(
        app_path('Services/Procurement/QuotationToPurchaseOrderService.php')
    );

    expect($service)
        ->toContain("application_type !== 'asset'")
        ->toContain("'meter_type'")
        ->toContain("'meter_reading'")
        ->toContain("'meter_recorded_at'")
        ->toContain('Informe hodômetro ou horímetro');
});

it('inclui leitura de ativo no mapa antes de gerar a OC', function (): void {
    $page = file_get_contents(
        app_path('Filament/Pages/QuotationComparison.php')
    );

    $view = file_get_contents(
        resource_path('views/filament/pages/quotation-comparison.blade.php')
    );

    expect($page)
        ->toContain('public array $meterReadings')
        ->toContain('getSelectedAssetItemsProperty')
        ->toContain('convert($this->quotation, $this->meterReadings)')
        ->and($view)
        ->toContain('Leituras dos ativos')
        ->toContain('Leitura atual')
        ->toContain('datetime-local');
});
