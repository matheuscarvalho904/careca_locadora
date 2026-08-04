<?php

it('registra as rotas do PDF do checklist', function (): void {
    $routes = file_get_contents(base_path('routes/web.php'));

    expect($routes)
        ->toContain('rental-deliveries.checklist-pdf')
        ->toContain('rental-returns.checklist-pdf')
        ->toContain('RentalDeliveryChecklistPdfController')
        ->toContain('RentalReturnChecklistPdfController');
});

it('integra as páginas premium aos resources', function (): void {
    $delivery = file_get_contents(
        app_path(
            'Filament/Resources/RentalDeliveries/RentalDeliveryResource.php'
        )
    );

    $return = file_get_contents(
        app_path(
            'Filament/Resources/RentalReturns/RentalReturnResource.php'
        )
    );

    expect($delivery)
        ->toContain('ManageDeliveryChecklistPremium')
        ->toContain("'checklist-premium'")
        ->and($return)
        ->toContain('ManageReturnChecklistPremium')
        ->toContain("'checklist-premium'");
});
