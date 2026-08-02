<?php

it('expande o cadastro de produtos e adiciona seeders mestres', function (): void {
    $migration = file_get_contents(
        database_path(
            'migrations/2026_08_02_160000_expand_products_and_create_catalog_tables.php'
        )
    );

    $resource = file_get_contents(
        app_path('Filament/Resources/Products/ProductResource.php')
    );

    expect($migration)
        ->toContain("Schema::create('product_categories'")
        ->toContain("Schema::create('product_brands'")
        ->toContain("'minimum_stock'")
        ->toContain("'default_warehouse_id'")
        ->toContain("'primary_supplier_id'")
        ->toContain("'ncm'");

    expect($resource)
        ->toContain("Tab::make('Geral')")
        ->toContain("Tab::make('Estoque')")
        ->toContain("Tab::make('Compras e custos')")
        ->toContain("Tab::make('Aplicação e financeiro')")
        ->toContain("Tab::make('Fiscal')")
        ->toContain("Tab::make('Arquivos e observações')");

    expect(file_exists(database_path('seeders/ProcurementMasterDataSeeder.php')))
        ->toBeTrue();
});
