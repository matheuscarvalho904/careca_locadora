<?php

it('disponibiliza geração de PDF da fatura de locação', function (): void {
    $controller = app_path(
        'Http/Controllers/Finance/RentalInvoicePdfController.php'
    );

    $view = resource_path('views/pdf/rental-invoice.blade.php');
    $page = app_path(
        'Filament/Resources/RentalInvoices/Pages/EditRentalInvoice.php'
    );

    expect(file_exists($controller))->toBeTrue()
        ->and(file_exists($view))->toBeTrue();

    expect(file_get_contents($controller))
        ->toContain("Pdf::loadView('pdf.rental-invoice'")
        ->toContain('Fatura-de-Locacao-');

    expect(file_get_contents($page))
        ->toContain("Action::make('downloadPdf')")
        ->toContain("route('rental-invoices.pdf'");

    expect(file_get_contents($view))
        ->toContain('FATURA DE LOCAÇÃO')
        ->toContain('não é documento fiscal')
        ->toContain('Condições de pagamento');
});
