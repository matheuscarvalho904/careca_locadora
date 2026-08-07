<?php
it('captura search na closure do catalogo por categoria', function (): void {
    $source = file_get_contents(
        app_path('Http/Controllers/Api/PublicCatalogController.php')
    );

    expect($source)
        ->toContain("->map(function (Collection \$group) use (\$search): array {")
        ->toContain('branchId: $search->branchId');
});
