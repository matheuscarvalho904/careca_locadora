<?php

it('possui CRUD completo de categorias de ativos', function (): void {
    $resource = file_get_contents(
        app_path(
            'Filament/Resources/AssetCategories/AssetCategoryResource.php'
        )
    );

    expect($resource)
        ->toContain("navigationLabel = 'Categorias de ativos'")
        ->toContain("TextInput::make('name')")
        ->toContain("TextInput::make('prefix')")
        ->toContain("Select::make('asset_type')")
        ->toContain("Select::make('meter_type')")
        ->toContain("Toggle::make('requires_plate')");
});

it('possui páginas de listar criar e editar categorias', function (): void {
    expect(file_exists(
        app_path(
            'Filament/Resources/AssetCategories/Pages/ListAssetCategories.php'
        )
    ))->toBeTrue()
        ->and(file_exists(
            app_path(
                'Filament/Resources/AssetCategories/Pages/CreateAssetCategory.php'
            )
        ))->toBeTrue()
        ->and(file_exists(
            app_path(
                'Filament/Resources/AssetCategories/Pages/EditAssetCategory.php'
            )
        ))->toBeTrue();
});
