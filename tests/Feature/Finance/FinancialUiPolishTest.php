<?php

it('mantém os títulos financeiros em português e o extrato organizado', function (): void {
    $cashResource = file_get_contents(
        app_path('Filament/Resources/CashMovements/CashMovementResource.php')
    );

    $cashPage = file_get_contents(
        app_path('Filament/Resources/CashMovements/Pages/ListCashMovements.php')
    );

    $cashTable = file_get_contents(
        app_path('Filament/Resources/CashMovements/Tables/CashMovementsTable.php')
    );

    $paymentPage = file_get_contents(
        app_path('Filament/Resources/FinancialPayments/Pages/ListFinancialPayments.php')
    );

    expect($cashResource)
        ->toContain("protected static ?string \$pluralModelLabel = 'movimentações financeiras'")
        ->toContain("protected static ?string \$navigationLabel = 'Extrato financeiro'");

    expect($cashPage)
        ->toContain("protected static ?string \$title = 'Extrato financeiro'");

    expect($paymentPage)
        ->toContain("protected static ?string \$title = 'Pagamentos'");

    expect($cashTable)
        ->toContain("->label('Descrição')")
        ->toContain("->width('320px')")
        ->toContain("->alignEnd()")
        ->toContain("->label('Status')")
        ->toContain("->striped()");
});
