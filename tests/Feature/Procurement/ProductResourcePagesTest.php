<?php

it('mantém nomes corretos das páginas de marcas e categorias', function (): void {
    $files = [
        app_path('Filament/Resources/ProductBrands/Pages/CreateProductBrand.php'),
        app_path('Filament/Resources/ProductBrands/Pages/EditProductBrand.php'),
        app_path('Filament/Resources/ProductCategories/Pages/CreateProductCategory.php'),
        app_path('Filament/Resources/ProductCategories/Pages/EditProductCategory.php'),
    ];

    foreach ($files as $file) {
        expect(file_exists($file))->toBeTrue();
    }

    expect(file_get_contents($files[0]))
        ->toContain('class CreateProductBrand extends CreateRecord');

    expect(file_get_contents($files[1]))
        ->toContain('class EditProductBrand extends EditRecord');

    expect(file_get_contents($files[2]))
        ->toContain('class CreateProductCategory extends CreateRecord');

    expect(file_get_contents($files[3]))
        ->toContain('class EditProductCategory extends EditRecord');
});
