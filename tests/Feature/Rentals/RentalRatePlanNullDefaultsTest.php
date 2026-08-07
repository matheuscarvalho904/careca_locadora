<?php

use App\Models\RentalRatePlan;

it('possui defaults para colunas numericas nao nulas', function (): void {
    $model = new RentalRatePlan();

    expect((float) $model->extra_distance_value)->toBe(0.0)
        ->and((float) $model->extra_hour_value)->toBe(0.0)
        ->and((float) $model->deposit_value)->toBe(0.0)
        ->and((float) $model->minimum_value)->toBe(0.0)
        ->and((int) $model->minimum_quantity)->toBe(1)
        ->and((int) $model->priority)->toBe(100);
});

it('normaliza nulos antes de persistir tarifa', function (): void {
    $source = file_get_contents(app_path('Models/RentalRatePlan.php'));

    expect($source)
        ->toContain('normalizeRequiredNumericFields')
        ->toContain("'extra_distance_value'")
        ->toContain("'extra_hour_value'")
        ->toContain("'minimum_value'")
        ->toContain("static::saving");
});

it('exige zero ou valor nos campos excedentes da tela', function (): void {
    $resource = file_get_contents(
        app_path(
            'Filament/Resources/RentalRatePlans/RentalRatePlanResource.php'
        )
    );

    expect($resource)
        ->toContain("TextInput::make('extra_distance_value')")
        ->toContain("TextInput::make('extra_hour_value')")
        ->toContain("->label('KM excedente')")
        ->toContain("->label('Hora excedente')");
});
