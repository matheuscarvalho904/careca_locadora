<?php

it('usa CSS próprio sem depender da compilação Tailwind', function (): void {
    $component = file_get_contents(
        resource_path('views/components/damage-map-canvas.blade.php')
    );

    expect($component)
        ->toContain('.dmg-modal-backdrop')
        ->toContain('.dmg-modal')
        ->toContain('.dmg-form')
        ->toContain('.dmg-select')
        ->toContain('color-scheme: dark')
        ->toContain('background: #0f1115 !important')
        ->toContain('color: #fff !important');
});

it('mantém o formulário em grade profissional e responsiva', function (): void {
    $component = file_get_contents(
        resource_path('views/components/damage-map-canvas.blade.php')
    );

    expect($component)
        ->toContain('grid-template-columns: repeat(2, minmax(0, 1fr))')
        ->toContain('@media (max-width: 900px)')
        ->toContain('.dmg-field--full')
        ->toContain('.dmg-modal-actions');
});
