<?php

namespace App\Services\Inventory;

use App\Models\InventoryBalance;
use App\Models\PurchaseReceiptItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class StockEntryService
{
    public function postPurchaseReceiptItem(PurchaseReceiptItem $receiptItem): ?StockMovement
    {
        return DB::transaction(function () use ($receiptItem): ?StockMovement {
            $receiptItem->loadMissing([
                'receipt.purchaseOrder',
                'purchaseOrderItem.product',
            ]);

            $orderItem = $receiptItem->purchaseOrderItem;
            $product = $orderItem?->product;

            if (! $orderItem || ! $product) {
                throw ValidationException::withMessages([
                    'stock' => 'Não foi possível identificar o produto da Ordem de Compra.',
                ]);
            }

            if (
                $orderItem->application_type !== 'stock'
                || ! $product->stock_controlled
            ) {
                return null;
            }

            $warehouseId = $receiptItem->warehouse_id
                ?: $orderItem->warehouse_id
                ?: $product->default_warehouse_id
                ?: $receiptItem->receipt?->warehouse_id;

            if (blank($warehouseId)) {
                throw ValidationException::withMessages([
                    'warehouse' => "Informe o almoxarifado para o produto {$product->name}.",
                ]);
            }

            $existing = StockMovement::query()
                ->where('purchase_receipt_item_id', $receiptItem->id)
                ->where('type', 'purchase_receipt')
                ->first();

            if ($existing) {
                return $existing;
            }

            $balance = InventoryBalance::query()
                ->where('organization_id', $receiptItem->organization_id)
                ->where('product_id', $receiptItem->product_id)
                ->where('warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->first();

            if (! $balance) {
                $balance = InventoryBalance::query()->create([
                    'organization_id' => $receiptItem->organization_id,
                    'product_id' => $receiptItem->product_id,
                    'warehouse_id' => $warehouseId,
                ]);

                $balance = InventoryBalance::query()
                    ->lockForUpdate()
                    ->findOrFail($balance->id);
            }

            $quantity = (float) $receiptItem->received_quantity;
            $unitCost = (float) $receiptItem->unit_value;

            $balanceBefore = (float) $balance->quantity_on_hand;
            $averageBefore = (float) $balance->average_cost;
            $valueBefore = $balanceBefore * $averageBefore;
            $entryValue = $quantity * $unitCost;

            $balanceAfter = $balanceBefore + $quantity;
            $averageAfter = $balanceAfter > 0
                ? ($valueBefore + $entryValue) / $balanceAfter
                : 0;

            $balance->update([
                'quantity_on_hand' => $balanceAfter,
                'average_cost' => $averageAfter,
                'inventory_value' => $balanceAfter * $averageAfter,
                'last_movement_at' => $receiptItem->receipt?->received_at ?? now(),
            ]);

            $product->updateQuietly([
                'last_purchase_cost' => $unitCost,
                'average_cost' => $this->calculateProductAverageCost(
                    organizationId: $receiptItem->organization_id,
                    productId: $receiptItem->product_id,
                ),
            ]);

            return StockMovement::query()->create([
                'organization_id' => $receiptItem->organization_id,
                'product_id' => $receiptItem->product_id,
                'warehouse_id' => $warehouseId,
                'purchase_receipt_id' => $receiptItem->purchase_receipt_id,
                'purchase_receipt_item_id' => $receiptItem->id,
                'purchase_order_id' => $receiptItem->receipt?->purchase_order_id,
                'created_by' => auth()->id(),
                'type' => 'purchase_receipt',
                'direction' => 'in',
                'source_type' => PurchaseReceiptItem::class,
                'source_id' => $receiptItem->id,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $entryValue,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'average_cost_before' => $averageBefore,
                'average_cost_after' => $averageAfter,
                'occurred_at' => $receiptItem->receipt?->received_at ?? now(),
                'notes' => "Entrada pelo recebimento {$receiptItem->receipt?->number}.",
                'metadata' => [
                    'purchase_receipt_number' => $receiptItem->receipt?->number,
                    'purchase_order_number' => $receiptItem->receipt?->purchaseOrder?->number,
                    'invoice_number' => $receiptItem->receipt?->invoice_number,
                    'batch_number' => $receiptItem->batch_number,
                    'expires_at' => $receiptItem->expires_at?->toDateString(),
                    'serial_number' => $receiptItem->serial_number,
                ],
            ]);
        });
    }

    private function calculateProductAverageCost(
        string $organizationId,
        string $productId,
    ): float {
        $balances = InventoryBalance::query()
            ->where('organization_id', $organizationId)
            ->where('product_id', $productId)
            ->get();

        $totalQuantity = (float) $balances->sum('quantity_on_hand');

        if ($totalQuantity <= 0) {
            return 0;
        }

        $totalValue = (float) $balances->sum('inventory_value');

        return $totalValue / $totalQuantity;
    }
}
