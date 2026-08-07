<?php

it('possui campos comerciais da categoria sem nova migration', function (): void {
    $resource = file_get_contents(
        app_path(
            'Filament/Resources/AssetCategories/AssetCategoryResource.php'
        )
    );

    expect($resource)
        ->toContain("metadata.public_title")
        ->toContain("metadata.similar_models")
        ->toContain("metadata.commercial_description")
        ->toContain("metadata.cover_image")
        ->toContain("metadata.featured");
});
