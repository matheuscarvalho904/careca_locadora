<?php

it('possui páginas premium de entrega e devolução', function (): void {
    expect(file_exists(
        app_path(
            'Filament/Resources/RentalDeliveries/Pages/ManageDeliveryChecklistPremium.php'
        )
    ))->toBeTrue()
        ->and(file_exists(
            app_path(
                'Filament/Resources/RentalReturns/Pages/ManageReturnChecklistPremium.php'
            )
        ))->toBeTrue();
});

it('possui assinatura desenhada em canvas', function (): void {
    $view = file_get_contents(
        resource_path('views/components/checklist-premium-panel.blade.php')
    );

    expect($view)
        ->toContain('<canvas')
        ->toContain("toDataURL('image/png')")
        ->toContain('saveSignature')
        ->toContain('touch-action:none');
});

it('possui fotos vinculadas às avarias', function (): void {
    $trait = file_get_contents(
        app_path('Filament/Concerns/InteractsWithChecklistPremium.php')
    );

    $view = file_get_contents(
        resource_path('views/components/checklist-premium-panel.blade.php')
    );

    expect($trait)
        ->toContain('saveDamagePhotos')
        ->toContain('rental-damage-photos')
        ->toContain('deleteDamagePhoto')
        ->and($view)
        ->toContain('capture="environment"');
});

it('possui PDF premium de entrega e devolução', function (): void {
    expect(file_exists(
        app_path(
            'Http/Controllers/Rentals/RentalDeliveryChecklistPdfController.php'
        )
    ))->toBeTrue()
        ->and(file_exists(
            app_path(
                'Http/Controllers/Rentals/RentalReturnChecklistPdfController.php'
            )
        ))->toBeTrue()
        ->and(file_exists(
            resource_path('views/pdf/rental-checklist-premium.blade.php')
        ))->toBeTrue();
});

it('inclui mapas, avarias, fotos, assinaturas e hash no PDF', function (): void {
    $pdf = file_get_contents(
        resource_path('views/pdf/rental-checklist-premium.blade.php')
    );

    expect($pdf)
        ->toContain('Mapa visual de avarias')
        ->toContain('Relação de avarias')
        ->toContain('Fotos das avarias')
        ->toContain('Ciência e assinaturas')
        ->toContain('$document_hash');
});

it('não exige nova migration para fechar o módulo', function (): void {
    expect(glob(
        database_path('migrations/*checklist*premium*.php')
    ))->toBeEmpty();
});
