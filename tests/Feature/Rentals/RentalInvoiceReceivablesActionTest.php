<?php

it('permite gerar contas a receber para fatura já marcada como emitida', function (): void {
    $path = app_path(
        'Filament/Resources/RentalInvoices/Pages/EditRentalInvoice.php'
    );

    $source = file_get_contents($path);

    expect($source)
        ->toContain("'Gerar contas a receber'")
        ->toContain('$this->record->receivables()->doesntExist()')
        ->toContain("\$this->record->status !== 'cancelled'");
});

it('mantém a fatura em rascunho antes da emissão', function (): void {
    $path = app_path('Services/Rentals/RentalInvoiceService.php');

    $source = file_get_contents($path);

    expect($source)
        ->toContain("'status' => 'draft'")
        ->toContain("'issued_at' => null");
});
