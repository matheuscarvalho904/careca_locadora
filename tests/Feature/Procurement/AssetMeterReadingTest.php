<?php

use App\Services\Procurement\ProcurementValidationService;
use Illuminate\Validation\ValidationException;

it('exige leitura de hodômetro ou horímetro quando a aplicação é ativo', function (): void {
    $service = app(ProcurementValidationService::class);

    expect(fn () => $service->validatePurchaseItem([
        'product_id' => 'produto',
        'application_type' => 'asset',
        'asset_id' => 'ativo',
        'quantity' => 1,
        'unit_value' => 10,
    ]))->toThrow(ValidationException::class);

    expect(fn () => $service->validatePurchaseItem([
        'product_id' => 'produto',
        'application_type' => 'asset',
        'asset_id' => 'ativo',
        'meter_type' => 'odometer',
        'meter_reading' => 12500,
        'quantity' => 1,
        'unit_value' => 10,
    ]))->not->toThrow(ValidationException::class);
});

it('adiciona os campos de leitura nas telas de OC e OS', function (): void {
    $files = [
        app_path('Filament/Resources/PurchaseOrders/PurchaseOrderResource.php'),
        app_path('Filament/Resources/ServiceOrders/ServiceOrderResource.php'),
    ];

    foreach ($files as $file) {
        $resource = file_get_contents($file);

        expect($resource)
            ->toContain("Select::make('meter_type')")
            ->toContain("'odometer' => 'Hodômetro'")
            ->toContain("'hourmeter' => 'Horímetro'")
            ->toContain("TextInput::make('meter_reading')")
            ->toContain("DateTimePicker::make('meter_recorded_at')");
    }
});
