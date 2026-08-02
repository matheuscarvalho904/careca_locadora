<?php

it('mantém as transições do contrato alinhadas ao fluxo de entrega e devolução', function (): void {
    $path = app_path(
        'Filament/Resources/RentalContracts/Pages/EditRentalContract.php'
    );

    $source = file_get_contents($path);

    expect($source)
        ->toContain("'status' => 'awaiting_signature'")
        ->toContain("'status' => 'active'")
        ->toContain("'signed_at' =>")
        ->toContain("'activated_at' =>")
        ->toContain("Action::make('startDelivery')")
        ->toContain("Action::make('startReturn')")
        ->toContain('RentalDeliveryService::class')
        ->toContain('RentalReturnService::class')
        ->toContain('RentalDeliveryResource::getUrl')
        ->toContain('RentalReturnResource::getUrl')
        ->toContain('$this->reloadRecordPage();')
        ->toContain('$this->record->refresh();')
        ->not->toContain("Action::make('close')");
});
