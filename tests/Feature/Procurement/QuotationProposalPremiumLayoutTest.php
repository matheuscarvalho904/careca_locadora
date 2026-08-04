<?php

it('organiza o lançamento da proposta de forma responsiva', function (): void {
    $view = file_get_contents(
        resource_path('views/filament/pages/quotation-proposal-entry.blade.php')
    );

    expect($view)
        ->toContain('.proposal-grid')
        ->toContain('.items-desktop')
        ->toContain('.items-mobile')
        ->toContain('.proposal-item-card')
        ->toContain('.summary-card')
        ->toContain('Voltar ao comparativo')
        ->toContain('Salvar proposta');
});

it('evita que observações e campos fiquem cortados', function (): void {
    $view = file_get_contents(
        resource_path('views/filament/pages/quotation-proposal-entry.blade.php')
    );

    expect($view)
        ->toContain('min-width: 980px')
        ->toContain('overflow-x: auto')
        ->toContain('min-w-[240px]')
        ->toContain('@media (max-width: 900px)');
});
