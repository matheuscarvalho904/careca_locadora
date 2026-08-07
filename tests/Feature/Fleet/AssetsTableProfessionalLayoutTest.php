<?php

it('mantém a tabela de ativos compacta e profissional', function (): void {
    $table = file_get_contents(
        app_path('Filament/Resources/Assets/Tables/AssetsTable.php')
    );

    expect($table)
        ->toContain("TextColumn::make('name')")
        ->toContain('->limit(42)')
        ->toContain("TextColumn::make('branch.name')")
        ->toContain("TextColumn::make('category.name')")
        ->toContain('isToggledHiddenByDefault: true')
        ->toContain('->striped()');
});
