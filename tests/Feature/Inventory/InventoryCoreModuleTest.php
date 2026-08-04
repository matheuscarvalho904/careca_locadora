<?php
it('cria saldos materializados e kardex', function (): void {
    $migration = file_get_contents(database_path('migrations/2026_08_03_040000_create_inventory_balances_and_stock_movements.php'));
    expect($migration)->toContain("Schema::create('inventory_balances'")->toContain("Schema::create('stock_movements'")->toContain("'quantity_on_hand'")->toContain("'average_cost'")->toContain("'balance_before'")->toContain("'balance_after'");
});
it('gera entrada automática apenas para itens destinados ao estoque', function (): void {
    $service = file_get_contents(app_path('Services/Inventory/StockEntryService.php'));
    $normalized = preg_replace('/\\s+/', '', $service);
    expect($normalized)->toContain("\$orderItem->application_type!=='stock'")->toContain('!$product->stock_controlled')->toContain("where('purchase_receipt_item_id'")->toContain("'type'=>'purchase_receipt'")->toContain("'direction'=>'in'");
});
it('integra o estoque à confirmação do recebimento', function (): void {
    $service = file_get_contents(app_path('Services/Procurement/PurchaseReceiptService.php'));
    $normalized = preg_replace('/\\s+/', '', $service);
    expect($normalized)->toContain('StockEntryService')->toContain('postPurchaseReceiptItem')->toContain("'status'=>'confirmed'");
});
it('fornece telas de saldo e kardex', function (): void {
    expect(file_exists(app_path('Filament/Resources/InventoryBalances/InventoryBalanceResource.php')))->toBeTrue()->and(file_exists(app_path('Filament/Resources/StockMovements/StockMovementResource.php')))->toBeTrue();
});
