<?php

it('mantém a seção de fotos das avarias no PDF', function (): void {
    $pdf = file_get_contents(
        resource_path('views/pdf/rental-checklist-premium.blade.php')
    );

    expect($pdf)
        ->toContain('Fotos das avarias')
        ->toContain('Nenhuma fotografia registrada para esta vistoria.')
        ->toContain('$damagePhotos')
        ->toContain('privateDataUri')
        ->toContain('$damageLabels')
        ->toContain('$conditionLabels')
        ->toContain('$severityLabels');
});

it('mantém o acabamento da galeria de evidências', function (): void {
    $pdf = file_get_contents(
        resource_path('views/pdf/rental-checklist-premium.blade.php')
    );

    expect($pdf)
        ->toContain('.photo-grid')
        ->toContain('.photo-card')
        ->toContain('.photo-caption')
        ->toContain('.empty-evidence');
});
