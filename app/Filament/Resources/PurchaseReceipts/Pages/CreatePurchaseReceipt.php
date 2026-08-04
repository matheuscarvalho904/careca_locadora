<?php
namespace App\Filament\Resources\PurchaseReceipts\Pages;

use App\Filament\Resources\PurchaseReceipts\PurchaseReceiptResource;
use App\Models\PurchaseOrder;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseReceipt extends CreateRecord
{
    protected static string $resource = PurchaseReceiptResource::class;

    public function mount(): void
    {
        parent::mount();

        $orderId = request()->query('purchase_order_id');

        if (blank($orderId)) {
            return;
        }

        $order = PurchaseOrder::query()->with(['items.product'])->find($orderId);

        if (! $order) {
            return;
        }

        $this->form->fill([
            'organization_id' => $order->organization_id,
            'purchase_order_id' => $order->id,
            'supplier_id' => $order->supplier_id,
            'received_by' => auth()->id(),
            'received_at' => now(),
            'status' => 'draft',
            'discount_value' => 0,
            'freight_value' => 0,
            'additional_value' => 0,
            'items' => $order->items
                ->filter(fn ($item): bool =>
                    (float) $item->received_quantity < (float) $item->quantity
                )
                ->map(fn ($item): array => [
                    'organization_id' => $order->organization_id,
                    'purchase_order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'warehouse_id' => $item->warehouse_id,
                    'ordered_quantity' => $item->quantity,
                    'previous_received_quantity' => $item->received_quantity,
                    'received_quantity' => max(
                        0,
                        (float) $item->quantity - (float) $item->received_quantity
                    ),
                    'pending_quantity' => 0,
                    'unit_value' => $item->unit_value,
                    'discount_value' => 0,
                    'accepted' => true,
                ])->values()->all(),
        ]);
    }
}
