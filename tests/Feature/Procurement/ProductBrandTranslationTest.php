<?php

it('traduz os status da tela de marcas', function (): void {
    $resource = file_get_contents(
        app_path(
            'Filament/Resources/ProductBrands/ProductBrandResource.php'
        )
    );

    expect($resource)
        ->toContain("'active' => 'Ativa'")
        ->toContain("'inactive' => 'Inativa'")
        ->toContain("->formatStateUsing")
        ->toContain("->color");
});
