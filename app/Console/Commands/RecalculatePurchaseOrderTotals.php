<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use App\Services\Procurement\PurchaseOrderTotalsService;
use Illuminate\Console\Command;

class RecalculatePurchaseOrderTotals extends Command
{
    protected $signature = 'procurement:recalculate-purchase-orders {--number=}';
    protected $description = 'Recalcula subtotal e total das Ordens de Compra existentes';

    public function handle(PurchaseOrderTotalsService $service): int
    {
        $query = PurchaseOrder::query()->with('items');

        if (filled($this->option('number'))) {
            $query->where('number', $this->option('number'));
        }

        $count = 0;

        $query->chunkById(100, function ($orders) use ($service, &$count): void {
            foreach ($orders as $order) {
                $service->recalculate($order);
                $count++;
            }
        });

        $this->info("{$count} Ordem(ns) de Compra recalculada(s).");

        return self::SUCCESS;
    }
}
