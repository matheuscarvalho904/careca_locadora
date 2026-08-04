<?php

it('mantém o modal do mapa independente do Tailwind compilado', function (): void {
    $component = file_get_contents(
        resource_path('views/components/damage-map-canvas.blade.php')
    );

    expect($component)
        ->toContain('@once')
        ->toContain('<style>')
        ->toContain('.dmg-modal-backdrop')
        ->toContain('.dmg-modal')
        ->toContain('.dmg-form')
        ->toContain('.dmg-select')
        ->toContain('.dmg-input')
        ->toContain('.dmg-textarea')
        ->toContain('.dmg-btn');
});

it('mantém o modal centralizado e acima do painel', function (): void {
    $component = file_get_contents(
        resource_path('views/components/damage-map-canvas.blade.php')
    );

    $normalized = preg_replace('/\s+/', '', $component);

    expect($normalized)
        ->toContain('.dmg-modal-backdrop{position:fixed;inset:0;z-index:9999;')
        ->toContain('display:flex;align-items:center;justify-content:center;')
        ->toContain('background:rgba(0,0,0,.78);');
});

it('mantém selects e options com contraste explícito', function (): void {
    $component = file_get_contents(
        resource_path('views/components/damage-map-canvas.blade.php')
    );

    $normalized = preg_replace('/\s+/', '', $component);

    expect($normalized)
        ->toContain('.dmg-select{appearance:auto;color-scheme:dark;cursor:pointer;}')
        ->toContain('.dmg-selectoption{background:#111318!important;color:#fff!important;}');
});

it('mantém responsividade para tablet e celular', function (): void {
    $component = file_get_contents(
        resource_path('views/components/damage-map-canvas.blade.php')
    );

    expect($component)
        ->toContain('@media (max-width: 900px)')
        ->toContain('@media (max-width: 560px)')
        ->toContain('grid-template-columns: 1fr')
        ->toContain('width: 100%');
});
