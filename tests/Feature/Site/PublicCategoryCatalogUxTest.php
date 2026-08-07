<?php

it('exibe categorias e tarifas no site público', function (): void {
    $page = file_get_contents(
        resource_path('js/pages/welcome.tsx')
    );

    expect($page)
        ->toContain('type CategoryOffer')
        ->toContain('Pesquisar categorias')
        ->toContain('Catálogo por categoria')
        ->toContain('available_count')
        ->toContain('fifteen_days')
        ->toContain('representative_asset_id')
        ->toContain('5562982887249');
});
