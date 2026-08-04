<?php

namespace App\Services\Procurement;

use App\Models\PurchaseOrder;

final class PurchaseOrderTotalsService
{
    public function recalculate(PurchaseOrder $order): PurchaseOrder
    {
        $subtotal = (float) $order->items()->sum('total_value');

        $total = max(
            0,
            $subtotal
            + (float) $order->freight_value
            + (float) $order->additional_value
            - (float) $order->discount_value
        );

        $order->updateQuietly([
            'subtotal' => $subtotal,
            'total_value' => $total,
        ]);

        return $order->refresh();
    }
}
