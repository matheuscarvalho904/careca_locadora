<?php

it('corrige a visibilidade dos selects das cotações', function (): void {
    $entry = file_get_contents(
        resource_path('views/filament/pages/quotation-proposal-entry.blade.php')
    );

    $comparison = file_get_contents(
        resource_path('views/filament/pages/quotation-comparison.blade.php')
    );

    foreach ([$entry, $comparison] as $view) {
        expect($view)
            ->toContain('.careca-select')
            ->toContain('.careca-select option')
            ->toContain('background: #ffffff')
            ->toContain('color: #111827');
    }
});

it('aplica layout premium no mapa comparativo', function (): void {
    $view = file_get_contents(
        resource_path('views/filament/pages/quotation-comparison.blade.php')
    );

    expect($view)
        ->toContain('.comparison-table')
        ->toContain('.proposal-card')
        ->toContain('.best-badge')
        ->toContain('Total da proposta')
        ->toContain('Condição não informada');
});
