<?php
namespace App\Services\Procurement;
use App\Models\PurchaseReceipt;
use App\Services\Finance\PurchaseReceiptPayableService;
use App\Services\Inventory\StockEntryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PurchaseReceiptService {
 public function confirm(PurchaseReceipt $receipt):PurchaseReceipt {
  return DB::transaction(function()use($receipt):PurchaseReceipt{
   $receipt=PurchaseReceipt::query()->with([
    'purchaseOrder.items.product','purchaseOrder.paymentCondition',
    'items.purchaseOrderItem.product','supplier.bankAccounts.bank'
   ])->lockForUpdate()->findOrFail($receipt->id);

   if($receipt->status==='confirmed'){
    app(PurchaseReceiptPayableService::class)->generate($receipt);
    return $receipt;
   }
   if($receipt->status!=='draft'){
    throw ValidationException::withMessages(['receipt'=>'Somente recebimentos em rascunho podem ser confirmados.']);
   }

   $order=$receipt->purchaseOrder;
   if(in_array($order->status,['cancelled','finished','received'],true)){
    throw ValidationException::withMessages(['purchase_order'=>'Esta Ordem de Compra não aceita novos recebimentos.']);
   }
   if($receipt->items->isEmpty()){
    throw ValidationException::withMessages(['items'=>'Inclua ao menos um item no recebimento.']);
   }

   $subtotal=0;
   foreach($receipt->items as $receiptItem){
    $orderItem=$order->items->firstWhere('id',$receiptItem->purchase_order_item_id);
    if(!$orderItem){
     throw ValidationException::withMessages(['items'=>'O item informado não pertence à Ordem de Compra.']);
    }
    $ordered=(float)$orderItem->quantity;
    $previous=(float)$orderItem->received_quantity;
    $current=(float)$receiptItem->received_quantity;
    $available=max(0,$ordered-$previous);

    if($current<=0){
     throw ValidationException::withMessages(['items'=>"A quantidade recebida do produto {$orderItem->product?->name} deve ser maior que zero."]);
    }
    if($current>$available){
     throw ValidationException::withMessages(['items'=>"A quantidade recebida do produto {$orderItem->product?->name} excede o saldo pendente de {$available}."]);
    }

    $newReceived=$previous+$current;
    $receiptItem->update([
     'organization_id'=>$receipt->organization_id,
     'product_id'=>$orderItem->product_id,
     'warehouse_id'=>$receiptItem->warehouse_id ?: $orderItem->warehouse_id ?: $receipt->warehouse_id,
     'ordered_quantity'=>$ordered,
     'previous_received_quantity'=>$previous,
     'pending_quantity'=>max(0,$ordered-$newReceived),
     'unit_value'=>$orderItem->unit_value,
    ]);
    $orderItem->updateQuietly(['received_quantity'=>$newReceived]);
    $subtotal+=(float)$receiptItem->fresh()->total_value;
   }

   $total=max(0,$subtotal+(float)$receipt->freight_value+(float)$receipt->additional_value-(float)$receipt->discount_value);
   $receipt->update([
    'status'=>'confirmed','subtotal'=>$subtotal,'total_value'=>$total,
    'confirmed_at'=>now(),'received_at'=>$receipt->received_at ?? now(),
    'received_by'=>$receipt->received_by ?? auth()->id(),
   ]);

   foreach($receipt->items()->with(['receipt.purchaseOrder','purchaseOrderItem.product'])->get() as $receiptItem){
    app(StockEntryService::class)->postPurchaseReceiptItem($receiptItem);
   }

   app(PurchaseReceiptPayableService::class)->generate($receipt->fresh());

   $allReceived=$order->items()->get()->every(
    fn($item):bool=>(float)$item->received_quantity>=(float)$item->quantity
   );
   $anyReceived=$order->items()->where('received_quantity','>',0)->exists();
   $order->update(['status'=>$allReceived?'received':($anyReceived?'partially_received':$order->status)]);

   return $receipt->fresh(['purchaseOrder','supplier','warehouse','items.product','accountsPayable']);
  });
 }
}
