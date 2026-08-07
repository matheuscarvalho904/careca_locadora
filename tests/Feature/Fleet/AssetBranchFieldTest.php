<?php

it('possui filial obrigatória no cadastro do ativo', function (): void {
    $form = file_get_contents(
        app_path('Filament/Resources/Assets/Schemas/AssetForm.php')
    );

    expect($form)
        ->toContain("Select::make('branch_id')")
        ->toContain("->label('Filial responsável')")
        ->toContain("titleAttribute: 'name'")
        ->toContain('->required()');
});

it('exibe e filtra a filial pelo nome operacional', function (): void {
    $table = file_get_contents(
        app_path('Filament/Resources/Assets/Tables/AssetsTable.php')
    );

    expect($table)
        ->toContain("TextColumn::make('branch.name')")
        ->toContain("SelectFilter::make('branch_id')")
        ->toContain("->relationship('branch', 'name')")
        ->not->toContain("branch.trade_name");
});
