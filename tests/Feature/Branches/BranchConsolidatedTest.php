<?php

it('usa nome operacional e código automático nas filiais', function (): void {
    $resource = file_get_contents(
        app_path('Filament/Resources/Branches/BranchResource.php')
    );

    $model = file_get_contents(app_path('Models/Branch.php'));

    expect($resource)
        ->toContain("TextInput::make('name')")
        ->toContain('Nome da filial')
        ->toContain('Gerado automaticamente')
        ->and($model)
        ->toContain("key: 'branch'")
        ->toContain("prefix: 'FL-'");
});
