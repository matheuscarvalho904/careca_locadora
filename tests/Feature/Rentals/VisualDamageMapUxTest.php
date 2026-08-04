<?php

it('usa marcadores visuais em formato X', function (): void {
    $component = file_get_contents(
        resource_path('views/components/damage-map-canvas.blade.php')
    );

    expect($component)
        ->toContain('X amarelo — Preexistente')
        ->toContain('X vermelho — Nova')
        ->toContain('X laranja — Agravada')
        ->toContain('X verde — Reparada')
        ->toContain('×');
});

it('mantém selects legíveis no tema escuro com CSS isolado', function (): void {
    $component = file_get_contents(
        resource_path('views/components/damage-map-canvas.blade.php')
    );

    expect($component)
        ->toContain('.dmg-select')
        ->toContain('color-scheme: dark')
        ->toContain('background: #0f1115 !important')
        ->toContain('color: #fff !important')
        ->toContain('.dmg-select option')
        ->toContain('background: #111318 !important');
});

it('inclui diagramas técnicos mais realistas do veículo', function (): void {
    foreach (['front', 'rear', 'left', 'right', 'top'] as $view) {
        $svg = public_path(
            "images/inspection-diagrams/vehicle-{$view}.svg"
        );

        expect(file_exists($svg))->toBeTrue();

        $content = file_get_contents($svg);

        expect($content)
            ->toContain('<linearGradient')
            ->toContain('<feDropShadow')
            ->toContain('stroke="#334155"');
    }
});
