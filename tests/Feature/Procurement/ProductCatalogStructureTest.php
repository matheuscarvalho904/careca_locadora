<?php

it('mantém categorias simples sem autorreferência', function (): void {
    $migration = file_get_contents(
        database_path(
            'migrations/2026_08_02_160000_expand_products_and_create_catalog_tables.php'
        )
    );

    $model = file_get_contents(
        app_path('Models/ProductCategory.php')
    );

    $resource = file_get_contents(
        app_path(
            'Filament/Resources/ProductCategories/ProductCategoryResource.php'
        )
    );

    expect($migration)
        ->toContain("Schema::create('product_categories'")
        ->not->toContain("'parent_id'")
        ->not->toContain('product_categories_parent_id_foreign');

    expect($model)
        ->not->toContain('function parent(')
        ->not->toContain('function children(');

    expect($resource)
        ->not->toContain("Select::make('parent_id')")
        ->toContain("TextInput::make('code')")
        ->toContain("TextInput::make('name')");
});
