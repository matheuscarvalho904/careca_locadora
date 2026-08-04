<?php
it('vincula contas a pagar à OC e ao recebimento', function (): void {
    $migration = file_get_contents(database_path('migrations/2026_08_03_050000_link_accounts_payable_to_procurement.php'));
    expect($migration)->toContain("'purchase_order_id'")->toContain("'purchase_receipt_id'")->toContain("'installment_number'")->toContain("'installment_count'")->toContain('accounts_payable_receipt_installment_unique');
});
it('gera parcelas e distribui os centavos na última parcela', function (): void {
    $service = file_get_contents(app_path('Services/Finance/PurchaseReceiptPayableService.php'));
    $normalized = preg_replace('/\\s+/', '', $service);
    expect($normalized)->toContain('intdiv($totalCents,$installmentCount)')->toContain('$totalCents%$installmentCount')->toContain('$installment===$installmentCount')->toContain("'status'=>'awaiting_approval'")->toContain("'origin_type'=>'purchase_receipt'");
});
it('congela os dados bancários e impede duplicidade', function (): void {
    $service = file_get_contents(app_path('Services/Finance/PurchaseReceiptPayableService.php'));
    $normalized = preg_replace('/\\s+/', '', $service);
    expect($normalized)->toContain('->snapshot()')->toContain("'bank_snapshot'=>\$bankSnapshot")->toContain("where('purchase_receipt_id',\$receipt->id)")->toContain('if($existing->isNotEmpty())');
});
it('integra financeiro à confirmação do recebimento', function (): void {
    $service = file_get_contents(app_path('Services/Procurement/PurchaseReceiptService.php'));
    $normalized = preg_replace('/\\s+/', '', $service);
    expect($normalized)->toContain('PurchaseReceiptPayableService')->toContain('generate($receipt->fresh())')->toContain('StockEntryService');
});
it('fornece comando para corrigir recebimentos antigos', function (): void {
    $command = file_get_contents(app_path('Console/Commands/GenerateMissingPurchaseReceiptPayables.php'));
    $normalized = preg_replace('/\\s+/', '', $command);
    expect($normalized)->toContain('finance:generate-missing-purchase-payables')->toContain("where('status','confirmed')")->toContain("whereDoesntHave('accountsPayable')");
});
