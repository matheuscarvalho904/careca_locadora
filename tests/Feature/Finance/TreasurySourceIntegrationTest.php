<?php

it('adiciona origem polimórfica às movimentações financeiras', function (): void {
    $migration = file_get_contents(
        database_path(
            'migrations/2026_08_03_060000_add_source_to_cash_movements.php'
        )
    );

    expect($migration)
        ->toContain("'source_type'")
        ->toContain("'source_id'")
        ->toContain('cash_movements_org_source_idx')
        ->toContain('cash_movements_org_source_category_unique');
});

it('evita movimentação duplicada no pagamento e no estorno', function (): void {
    $service = file_get_contents(
        app_path('Services/Finance/PaymentService.php')
    );

    expect($service)
        ->toContain('firstOrCreate')
        ->toContain("'category' => 'payment'")
        ->toContain("'category' => 'payment_reversal'")
        ->toContain('postPaymentCashMovement')
        ->toContain('postReversalCashMovement');
});

it('mantém pagamento e caixa na mesma transação', function (): void {
    $service = file_get_contents(
        app_path('Services/Finance/PaymentService.php')
    );

    expect($service)
        ->toContain('DB::transaction')
        ->toContain('lockForUpdate')
        ->toContain('$this->postPaymentCashMovement')
        ->toContain('$this->synchronize($payable)');
});

it('permite regularizar movimentações ausentes', function (): void {
    $command = file_get_contents(
        app_path(
            'Console/Commands/RepairFinancialPaymentCashMovements.php'
        )
    );

    expect($command)
        ->toContain('finance:repair-payment-cash-movements')
        ->toContain('postPaymentCashMovement')
        ->toContain('postReversalCashMovement');
});
