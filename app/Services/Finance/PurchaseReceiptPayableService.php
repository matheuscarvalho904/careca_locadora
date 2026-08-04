<?php
namespace App\Services\Finance;
use App\Models\AccountPayable;
use App\Models\BankAccount;
use App\Models\PurchaseReceipt;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PurchaseReceiptPayableService {
 /** @return Collection<int, AccountPayable> */
 public function generate(PurchaseReceipt $receipt): Collection {
  return DB::transaction(function () use ($receipt): Collection {
   $receipt=PurchaseReceipt::query()->with([
    'supplier.bankAccounts.bank','purchaseOrder.paymentCondition','purchaseOrder.items'
   ])->lockForUpdate()->findOrFail($receipt->id);

   if($receipt->status!=='confirmed'){
    throw ValidationException::withMessages(['receipt'=>'O recebimento precisa estar confirmado para gerar o Contas a Pagar.']);
   }

   $existing=AccountPayable::query()->where('purchase_receipt_id',$receipt->id)->orderBy('installment_number')->get();
   if($existing->isNotEmpty()){return $existing;}

   $order=$receipt->purchaseOrder;
   $condition=$order?->paymentCondition;
   $installmentCount=max(1,(int)($condition?->installments ?? $order?->installments ?? 1));
   $intervalDays=max(0,(int)($condition?->interval_days ?? $order?->installment_interval_days ?? 30));
   $firstDueDays=max(0,(int)($condition?->first_due_days ?? 0));
   $baseDate=CarbonImmutable::parse($receipt->invoice_issued_at ?? $receipt->received_at ?? now())->startOfDay();
   $firstDueDate=filled($order?->first_due_date)?CarbonImmutable::parse($order->first_due_date):$baseDate->addDays($firstDueDays);
   $totalCents=(int)round((float)$receipt->total_value*100);
   if($totalCents<=0){
    throw ValidationException::withMessages(['total_value'=>'O recebimento não possui valor financeiro para gerar o Contas a Pagar.']);
   }

   $baseInstallmentCents=intdiv($totalCents,$installmentCount);
   $remainderCents=$totalCents%$installmentCount;
   $bankAccount=$this->resolveSupplierBankAccount($receipt);
   $bankSnapshot=$bankAccount?->snapshot();

   $costCenters=$order?->items->pluck('cost_center_id')->filter()->unique() ?? collect();
   $assets=$order?->items->pluck('asset_id')->filter()->unique() ?? collect();
   $costCenterId=$costCenters->count()===1?$costCenters->first():null;
   $assetId=$assets->count()===1?$assets->first():null;
   $titles=collect();

   for($installment=1;$installment<=$installmentCount;$installment++){
    $valueCents=$baseInstallmentCents+($installment===$installmentCount?$remainderCents:0);
    $dueDate=$firstDueDate->addDays(($installment-1)*$intervalDays);
    $base=$receipt->invoice_number ?: $receipt->number;
    $document=$installmentCount>1?"{$base}-{$installment}/{$installmentCount}":$base;

    $titles->push(AccountPayable::query()->create([
     'organization_id'=>$receipt->organization_id,
     'supplier_id'=>$receipt->supplier_id,
     'cost_center_id'=>$costCenterId,
     'asset_id'=>$assetId,
     'purchase_order_id'=>$receipt->purchase_order_id,
     'purchase_receipt_id'=>$receipt->id,
     'bank_account_id'=>$bankAccount?->id,
     'bank_snapshot'=>$bankSnapshot,
     'installment_number'=>$installment,
     'installment_count'=>$installmentCount,
     'origin_type'=>'purchase_receipt',
     'document_number'=>$document,
     'status'=>'awaiting_approval',
     'issued_at'=>$receipt->invoice_issued_at ?? $receipt->received_at?->toDateString() ?? today(),
     'competence_date'=>$receipt->invoice_issued_at ?? $receipt->received_at?->toDateString() ?? today(),
     'due_at'=>$dueDate,
     'original_value'=>$valueCents/100,
     'open_value'=>$valueCents/100,
     'attachment_path'=>$receipt->attachment_path,
     'notes'=>"Gerado automaticamente pelo recebimento {$receipt->number} da OC {$order?->number}.",
     'metadata'=>[
      'source'=>'purchase_receipt','purchase_receipt_id'=>$receipt->id,
      'purchase_receipt_number'=>$receipt->number,'purchase_order_id'=>$order?->id,
      'purchase_order_number'=>$order?->number,'invoice_number'=>$receipt->invoice_number,
      'invoice_series'=>$receipt->invoice_series,'invoice_access_key'=>$receipt->invoice_access_key,
      'installment_number'=>$installment,'installment_count'=>$installmentCount,
      'payment_condition_id'=>$order?->payment_condition_id,
      'payment_condition_name'=>$condition?->name,'generated_at'=>now()->toIso8601String(),
     ],
    ]));
   }
   return $titles;
  });
 }

 private function resolveSupplierBankAccount(PurchaseReceipt $receipt): ?BankAccount {
  return $receipt->supplier?->bankAccounts?->where('status','active')
   ->sortByDesc(fn(BankAccount $account):int=>($account->is_primary?100:0)+($account->use_for_payments?10:0))
   ->first();
 }
}
