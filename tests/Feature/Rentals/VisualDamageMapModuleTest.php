<?php

it('cria diagramas, marcações e fotos', function (): void {
    $migration = file_get_contents(
        database_path(
            'migrations/2026_08_03_070000_create_visual_damage_map_tables.php'
        )
    );

    expect($migration)
        ->toContain("Schema::create('inspection_diagram_templates'")
        ->toContain("Schema::create('inspection_diagram_views'")
        ->toContain("Schema::create('rental_damage_marks'")
        ->toContain("Schema::create('rental_damage_photos'");
});

it('inclui seeder por categoria e tipo de ativo', function (): void {
    $seeder = file_get_contents(
        database_path('seeders/InspectionDiagramSeeder.php')
    );

    expect($seeder)
        ->toContain('AssetCategory::query()')
        ->toContain("'vehicle'")
        ->toContain("'truck'")
        ->toContain("'motorcycle'")
        ->toContain("'equipment'")
        ->toContain("'front'")
        ->toContain("'rear'")
        ->toContain("'left'")
        ->toContain("'right'")
        ->toContain("'top'");
});

it('calcula valor somente para avarias novas ou agravadas', function (): void {
    $service = file_get_contents(
        app_path('Services/Rentals/DamageMapService.php')
    );

    $normalized = preg_replace('/\s+/', '', $service);

    expect($normalized)
        ->toContain("whereIn('condition',['new','aggravated'])")
        ->toContain("sum('estimated_value')")
        ->toContain("'damage_value'=>\$value");
});

it('adiciona relacionamentos aos itens de entrega e devolução', function (): void {
    $deliveryItem = file_get_contents(
        app_path('Models/RentalDeliveryItem.php')
    );

    $returnItem = file_get_contents(
        app_path('Models/RentalReturnItem.php')
    );

    $normalizedDeliveryItem = preg_replace('/\s+/', '', $deliveryItem);
    $normalizedReturnItem = preg_replace('/\s+/', '', $returnItem);

    expect($normalizedDeliveryItem)
        ->toContain('functiondamageMarks():MorphMany')
        ->toContain("morphMany(RentalDamageMark::class,'inspectable')")
        ->and($normalizedReturnItem)
        ->toContain('functiondamageMarks():MorphMany')
        ->toContain("morphMany(RentalDamageMark::class,'inspectable')");
});
