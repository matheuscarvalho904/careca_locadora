<?php

it('usa unidades compatíveis com o motor de preços', function (): void {
    $resource = file_get_contents(
        app_path(
            'Filament/Resources/RentalRatePlans/RentalRatePlanResource.php'
        )
    );

    expect($resource)
        ->toContain("'hourly' => 'Por hora'")
        ->toContain("'daily' => 'Diária'")
        ->toContain("'weekly' => 'Semanal'")
        ->toContain("'monthly' => 'Mensal'")
        ->toContain("'fixed' => 'Período fechado'")
        ->toContain("TextInput::make('minimum_quantity')");
});
