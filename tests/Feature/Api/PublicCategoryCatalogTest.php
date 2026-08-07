<?php

it('agrupa disponibilidade por categoria no catálogo público', function (): void {
    $controller = file_get_contents(
        app_path('Http/Controllers/Api/PublicCatalogController.php')
    );

    expect($controller)
        ->toContain("->groupBy('category_id')")
        ->toContain("'representative_asset_id'")
        ->toContain("'available_count'")
        ->toContain("'tariffs'")
        ->toContain("'fifteen_days'")
        ->toContain("'mode' => 'category'");
});

it('mantém filtro explícito pela organização pública', function (): void {
    $controller = file_get_contents(
        app_path('Http/Controllers/Api/PublicCatalogController.php')
    );

    expect($controller)
        ->toContain('withoutGlobalScopes()')
        ->toContain("where('organization_id', \$this->organizationId())");
});
