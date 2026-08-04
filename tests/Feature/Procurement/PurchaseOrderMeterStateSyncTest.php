<?php

it('reconstrói o estado das leituras em todos os ciclos do Livewire', function (): void {
    $page = file_get_contents(
        app_path('Filament/Pages/QuotationComparison.php')
    );

    expect($page)
        ->toContain('public function mount(): void')
        ->toContain('public function hydrate(): void')
        ->toContain('private function initializeMeterReadings(): void')
        ->toContain('private function normalizeMeterReadings(): void')
        ->toContain('$this->initializeMeterReadings();')
        ->toContain('$this->normalizeMeterReadings();');
});

it('sincroniza os campos antes da ação de gerar OC', function (): void {
    $view = file_get_contents(
        resource_path('views/filament/pages/quotation-comparison.blade.php')
    );

    expect($view)
        ->toContain('wire:model.live="meterReadings.')
        ->toContain('wire:model.live.debounce.250ms="meterReadings.')
        ->toContain('.meter_recorded_at"');
});

it('usa chaves de item normalizadas no serviço', function (): void {
    $service = file_get_contents(
        app_path('Services/Procurement/QuotationToPurchaseOrderService.php')
    );

    expect($service)
        ->toContain('$key = (string) $quotationItem->id')
        ->toContain('$meterReadings[$key] ?? []')
        ->toContain("array_key_exists('meter_reading', \$reading)");
});
