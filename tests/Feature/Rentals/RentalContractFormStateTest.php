<?php

it('não utiliza caminhos de relacionamento como campos persistíveis no contrato', function (): void {
    $formPath = app_path(
        'Filament/Resources/RentalContracts/Schemas/RentalContractForm.php'
    );

    $source = file_get_contents($formPath);

    expect($source)
        ->not->toContain("TextInput::make('customer.")
        ->not->toContain("TextInput::make('asset.")
        ->toContain("Placeholder::make('customer_display')")
        ->toContain("Placeholder::make('asset_prefix_display')")
        ->toContain("Placeholder::make('asset_name_display')");
});
