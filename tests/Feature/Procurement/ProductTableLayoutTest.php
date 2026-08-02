<?php

it('mantém a tabela de produtos sem quebra excessiva', function (): void {
    $resource = file_get_contents(
        app_path('Filament/Resources/Products/ProductResource.php')
    );

    expect($resource)
        ->toContain("->width('340px')")
        ->toContain("'class' => 'whitespace-nowrap'")
        ->toContain('->tooltip(fn (Product $record): string => $record->name)')
        ->toContain("'fuel' => 'Combustível'")
        ->toContain('->alignEnd()');
});
