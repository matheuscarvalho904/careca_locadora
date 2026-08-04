<?php
it('cria a estrutura de recebimentos parciais e totais', function (): void {
    $migration = file_get_contents(database_path('migrations/2026_08_03_030000_create_purchase_receipts.php'));
    expect($migration)->toContain("Schema::create('purchase_receipts'")->toContain("Schema::create('purchase_receipt_items'")->toContain("'received_quantity'")->toContain("'previous_received_quantity'")->toContain("'pending_quantity'");
});
it('bloqueia recebimento acima do saldo e confirmação duplicada', function (): void {
    $service = file_get_contents(app_path('Services/Procurement/PurchaseReceiptService.php'));
    $normalized = preg_replace('/\\s+/', '', $service);
    expect($normalized)->toContain("if(\$receipt->status==='confirmed')")->toContain('excedeosaldopendentede{$available}')->toContain("'status'=>'confirmed'")->toContain("'partially_received'")->toContain("'received'");
});
it('adiciona fluxo operacional na OC', function (): void {
    $page = file_get_contents(app_path('Filament/Resources/PurchaseOrders/Pages/EditPurchaseOrder.php'));
    expect($page)->toContain("Action::make('approve')")->toContain("Action::make('receive')")->toContain('Receber mercadoria')->toContain('purchase_order_id');
});
it('não cria estoque ou contas a pagar na migration de recebimentos', function (): void {
    $migration = file_get_contents(database_path('migrations/2026_08_03_030000_create_purchase_receipts.php'));
    expect($migration)->not->toContain("Schema::create('stock_movements'")->not->toContain("Schema::create('inventory_balances'")->not->toContain("Schema::create('accounts_payable'");
});
