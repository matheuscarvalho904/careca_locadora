<?php

it('mantém filial operacional no ativo e tarifa vinculada à categoria', function (): void {
    $assetForm = file_get_contents(
        app_path('Filament/Resources/Assets/Schemas/AssetForm.php')
    );

    $assetTable = file_get_contents(
        app_path('Filament/Resources/Assets/Tables/AssetsTable.php')
    );

    $rateResource = file_get_contents(
        app_path(
            'Filament/Resources/RentalRatePlans/RentalRatePlanResource.php'
        )
    );

    expect($assetForm)
        ->toContain("Select::make('branch_id')")
        ->toContain("titleAttribute: 'name'")
        ->and($assetTable)
        ->toContain("TextColumn::make('branch.name')")
        ->toContain("->relationship('branch', 'name')")
        ->and($rateResource)
        ->toContain("Select::make('asset_category_id')")
        ->toContain("Select::make('branch_id')")
        ->toContain("TextInput::make('unit_value')");
});

it('mantém códigos automáticos de filial e tarifa', function (): void {
    $branch = file_get_contents(app_path('Models/Branch.php'));

    $rateCreate = file_get_contents(
        app_path(
            'Filament/Resources/RentalRatePlans/Pages/CreateRentalRatePlan.php'
        )
    );

    expect($branch)
        ->toContain("key: 'branch'")
        ->toContain("prefix: 'FL-'")
        ->and($rateCreate)
        ->toContain("key: 'rental_rate_plan'")
        ->toContain("prefix: 'TAR-'");
});
