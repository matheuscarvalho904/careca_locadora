<?php

it('possui relações de categoria e filial na tarifa', function (): void {
    $model = file_get_contents(app_path('Models/RentalRatePlan.php'));

    expect($model)
        ->toContain('function assetCategory()')
        ->toContain("AssetCategory::class")
        ->toContain("'asset_category_id'")
        ->toContain('function branch()')
        ->toContain('Branch::class');
});

it('gera o código da tarifa na página de criação', function (): void {
    $page = file_get_contents(
        app_path(
            'Filament/Resources/RentalRatePlans/Pages/CreateRentalRatePlan.php'
        )
    );

    expect($page)
        ->toContain('mutateFormDataBeforeCreate')
        ->toContain("key: 'rental_rate_plan'")
        ->toContain("prefix: 'TAR-'")
        ->toContain('NumberSequenceService::class');
});
