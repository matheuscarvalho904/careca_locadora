<?php
it('registra a estrutura do contas a pagar', function (): void {
    expect(file_exists(app_path('Models/AccountPayable.php')))->toBeTrue()
        ->and(file_exists(app_path('Models/FinancialPayment.php')))->toBeTrue()
        ->and(file_exists(app_path('Services/Finance/PaymentService.php')))->toBeTrue()
        ->and(file_exists(app_path('Filament/Resources/AccountPayables/AccountPayableResource.php')))->toBeTrue();
    expect(file_get_contents(app_path('Models/AccountPayable.php')))->toContain("prefix: 'CP-'");
    expect(file_get_contents(app_path('Models/FinancialPayment.php')))->toContain("prefix: 'PAG-'");
    expect(file_get_contents(app_path('Services/Finance/PaymentService.php')))->toContain("'category'=>'payment'");
});
