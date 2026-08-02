<?php

it('exibe o botão para cadastrar conta financeira', function (): void {
    $path = app_path(
        'Filament/Resources/FinancialAccounts/Pages/ListFinancialAccounts.php'
    );

    $source = file_get_contents($path);

    expect($source)
        ->toContain('use Filament\Actions\CreateAction;')
        ->toContain('protected function getHeaderActions(): array')
        ->toContain("CreateAction::make()")
        ->toContain("->label('Nova conta financeira')");
});
