<?php

it('protege os itens gerados da devolução contra inclusão ou exclusão manual', function (): void {
    $path = app_path(
        'Filament/Resources/RentalReturns/Schemas/RentalReturnForm.php'
    );

    $source = file_get_contents($path);

    expect($source)
        ->toContain("Repeater::make('items')")
        ->toContain('->addable(false)')
        ->toContain('->deletable(false)')
        ->toContain('->reorderable(false)')
        ->toContain("Hidden::make('organization_id')")
        ->toContain("Hidden::make('delivery_item_id')")
        ->toContain("Hidden::make('contract_item_id')")
        ->toContain("Hidden::make('asset_id')");
});
