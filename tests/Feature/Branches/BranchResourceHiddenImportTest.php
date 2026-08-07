<?php

it('importa corretamente o componente Hidden no cadastro de filial', function (): void {
    $resource = file_get_contents(
        app_path('Filament/Resources/Branches/BranchResource.php')
    );

    expect($resource)
        ->toContain('use Filament\Forms\Components\Hidden;')
        ->not->toContain('App\Filament\Resources\Branches\Hidden');
});
