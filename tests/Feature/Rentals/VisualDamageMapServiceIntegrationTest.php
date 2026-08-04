<?php

it('fornece as avarias da entrega para comparação na devolução', function (): void {
    $service = file_get_contents(
        app_path('Services/Rentals/DamageMapService.php')
    );

    expect($service)
        ->toContain('function deliveryMarksForReturn')
        ->toContain('$returnItem->deliveryItem')
        ->toContain('->damageMarks()')
        ->toContain("where('status', 'active')")
        ->toContain("with([\n                'templateView',\n                'photos',");
});

it('valida o diagrama e a vista antes de criar uma marcação', function (): void {
    $service = file_get_contents(
        app_path('Services/Rentals/DamageMapService.php')
    );

    expect($service)
        ->toContain('function templateFor')
        ->toContain('Nenhum diagrama de inspeção foi configurado')
        ->toContain('A vista informada não pertence ao diagrama deste ativo');
});

it('sincroniza o valor de avarias novas e agravadas', function (): void {
    $service = file_get_contents(
        app_path('Services/Rentals/DamageMapService.php')
    );

    $normalized = preg_replace('/\s+/', '', $service);

    expect($normalized)
        ->toContain("whereIn('condition',['new','aggravated'])")
        ->toContain("sum('estimated_value')")
        ->toContain("'damage_value'=>\$value")
        ->toContain('RentalReturnService::class');
});

it('mantém criação e exclusão em transação', function (): void {
    $service = file_get_contents(
        app_path('Services/Rentals/DamageMapService.php')
    );

    expect(substr_count($service, 'DB::transaction'))->toBeGreaterThanOrEqual(2)
        ->and($service)
        ->toContain('function createMark')
        ->toContain('function deleteMark');
});
