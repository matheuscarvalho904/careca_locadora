<?php

it('recalcula e exibe o resumo consolidado da devolução', function (): void {
    $pagePath = app_path(
        'Filament/Resources/RentalReturns/Pages/EditRentalReturn.php'
    );

    $formPath = app_path(
        'Filament/Resources/RentalReturns/Schemas/RentalReturnForm.php'
    );

    $page = file_get_contents($pagePath);
    $form = file_get_contents($formPath);

    expect($page)
        ->toContain('protected function afterSave(): void')
        ->toContain('RentalReturnService::class')
        ->toContain('->recalculate($this->record->fresh())');

    expect($form)
        ->toContain("Placeholder::make('summary_total')")
        ->toContain("items()->sum('total_charge_value')")
        ->toContain("items()->sum('fuel_value')")
        ->toContain("items()->sum('damage_value')")
        ->toContain("items()->sum('cleaning_value')");
});
