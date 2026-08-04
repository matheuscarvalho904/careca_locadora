<?php

it('traduz os dados técnicos no PDF', function (): void {
    $pdf = file_get_contents(
        resource_path('views/pdf/rental-checklist-premium.blade.php')
    );

    expect($pdf)
        ->toContain("'new' => 'Nova'")
        ->toContain("'scratch' => 'Arranhão'")
        ->toContain("'light' => 'Leve'")
        ->toContain("'full' => 'Cheio'");
});

it('remove filtros SVG incompatíveis com o DomPDF', function (): void {
    $service = file_get_contents(
        app_path('Services/Rentals/ChecklistDocumentService.php')
    );

    expect($service)
        ->toContain("if (\$extension === 'svg')")
        ->toContain('preg_replace')
        ->toContain('fill="#f8fafc"');
});

it('centraliza a vista superior quando estiver sozinha', function (): void {
    $pdf = file_get_contents(
        resource_path('views/pdf/rental-checklist-premium.blade.php')
    );

    expect($pdf)
        ->toContain('.map-single')
        ->toContain('count($viewRow) === 1');
});
