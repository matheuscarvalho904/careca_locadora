<?php

it('possui CRUD de tarifas vinculado à categoria e filial', function (): void {
    $resource = file_get_contents(
        app_path(
            'Filament/Resources/RentalRatePlans/RentalRatePlanResource.php'
        )
    );

    expect($resource)
        ->toContain("Select::make('asset_category_id')")
        ->toContain("Select::make('branch_id')")
        ->toContain("Select::make('billing_unit')")
        ->toContain("TextInput::make('unit_value')")
        ->toContain("TextInput::make('deposit_value')")
        ->toContain("navigationLabel = 'Tarifas'");
});

it('possui relações de categoria e filial', function (): void {
    $model = file_get_contents(app_path('Models/RentalRatePlan.php'));

    expect($model)
        ->toContain('function assetCategory()')
        ->toContain('AssetCategory::class')
        ->toContain('function branch()')
        ->toContain('Branch::class');
});

it('gera o código automático na página de criação', function (): void {
    $page = file_get_contents(
        app_path(
            'Filament/Resources/RentalRatePlans/Pages/CreateRentalRatePlan.php'
        )
    );

    expect($page)
        ->toContain('mutateFormDataBeforeCreate')
        ->toContain("key: 'rental_rate_plan'")
        ->toContain("prefix: 'TAR-'");
});
