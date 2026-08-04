<?php

it('usa parâmetros resolvíveis nas closures da tabela', function (): void {
    $table = file_get_contents(
        app_path(
            'Filament/Resources/AccountPayables/Tables/AccountPayablesTable.php'
        )
    );

    expect($table)
        ->not->toContain('fn(string $s)')
        ->not->toContain('fn ($s)')
        ->toContain('fn (string $state): string')
        ->toContain('Aguardando aprovação');
});

it('oferece visão financeira profissional', function (): void {
    $table = file_get_contents(
        app_path(
            'Filament/Resources/AccountPayables/Tables/AccountPayablesTable.php'
        )
    );

    expect($table)
        ->toContain("'paid_value'")
        ->toContain("'open_value'")
        ->toContain("'purchaseOrder.number'")
        ->toContain("'purchaseReceipt.number'")
        ->toContain('Somente vencidas')
        ->toContain('Período de vencimento')
        ->toContain('paginated([10, 25, 50, 100])');
});

it('corrige textos do formulário e pagamento', function (): void {
    $form = file_get_contents(
        app_path(
            'Filament/Resources/AccountPayables/Schemas/AccountPayableForm.php'
        )
    );

    $page = file_get_contents(
        app_path(
            'Filament/Resources/AccountPayables/Pages/EditAccountPayable.php'
        )
    );

    expect($form)
        ->toContain('Número')
        ->toContain('Competência')
        ->toContain('Conta bancária do fornecedor')
        ->toContain('Observações')
        ->and($page)
        ->toContain('Cartão')
        ->toContain('Transferência')
        ->toContain('Observações');
});
