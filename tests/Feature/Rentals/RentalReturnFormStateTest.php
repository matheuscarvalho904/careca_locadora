<?php

it('mantém relacionamentos da devolução apenas para exibição', function (): void {
    $path = app_path(
        'Filament/Resources/RentalReturns/Schemas/RentalReturnForm.php'
    );

    $source = file_get_contents($path);

    expect($source)
        ->not->toContain("TextInput::make('contract.")
        ->not->toContain("TextInput::make('customer.")
        ->not->toContain("TextInput::make('asset.")
        ->toContain("Placeholder::make('contract_display')")
        ->toContain("Placeholder::make('customer_display')")
        ->toContain("Placeholder::make('asset_display')");
});
