<?php

it('possui páginas visuais para entrega e devolução', function (): void {
    expect(file_exists(
        app_path(
            'Filament/Resources/RentalDeliveries/Pages/ManageDeliveryDamageMap.php'
        )
    ))->toBeTrue()
        ->and(file_exists(
            app_path(
                'Filament/Resources/RentalReturns/Pages/ManageReturnDamageMap.php'
            )
        ))->toBeTrue();
});

it('possui componente clicável e responsivo', function (): void {
    $component = file_get_contents(
        resource_path('views/components/damage-map-canvas.blade.php')
    );

    expect($component)
        ->toContain('choose(event, viewId, viewName)')
        ->toContain('position_x')
        ->toContain('position_y')
        ->toContain('* 100')
        ->toContain('Salvar avaria');
});

it('integra as rotas às telas de entrega e devolução', function (): void {
    $deliveryResource = file_get_contents(
        app_path(
            'Filament/Resources/RentalDeliveries/RentalDeliveryResource.php'
        )
    );

    $returnResource = file_get_contents(
        app_path(
            'Filament/Resources/RentalReturns/RentalReturnResource.php'
        )
    );

    expect($deliveryResource)
        ->toContain('ManageDeliveryDamageMap')
        ->toContain("'damage-map'")
        ->and($returnResource)
        ->toContain('ManageReturnDamageMap')
        ->toContain("'damage-map'");
});

it('adiciona ações para abrir o mapa visual', function (): void {
    $deliveryPage = file_get_contents(
        app_path(
            'Filament/Resources/RentalDeliveries/Pages/EditRentalDelivery.php'
        )
    );

    $returnPage = file_get_contents(
        app_path(
            'Filament/Resources/RentalReturns/Pages/EditRentalReturn.php'
        )
    );

    expect($deliveryPage)
        ->toContain("Action::make('damage_map')")
        ->toContain('Mapa de avarias')
        ->and($returnPage)
        ->toContain("Action::make('damage_map')")
        ->toContain('Comparar avarias');
});
