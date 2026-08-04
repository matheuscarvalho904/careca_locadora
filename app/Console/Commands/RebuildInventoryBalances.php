<?php

namespace App\Console\Commands;

use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildInventoryBalances extends Command
{
    protected $signature = 'inventory:rebuild-balances';
    protected $description = 'Reconstrói os saldos de estoque com base no Kardex';

    public function handle(): int
    {
        DB::transaction(function (): void {
            InventoryBalance::query()->delete();

            StockMovement::query()
                ->orderBy('occurred_at')
                ->orderBy('created_at')
                ->chunkById(200, function ($movements): void {
                    foreach ($movements as $movement) {
                        $balance = InventoryBalance::query()->firstOrCreate([
                            'organization_id' => $movement->organization_id,
                            'product_id' => $movement->product_id,
                            'warehouse_id' => $movement->warehouse_id,
                        ]);

                        $quantity = (float) $movement->quantity;
                        $current = (float) $balance->quantity_on_hand;
                        $new = $movement->direction === 'out'
                            ? $current - $quantity
                            : $current + $quantity;

                        $balance->update([
                            'quantity_on_hand' => $new,
                            'average_cost' => $movement->average_cost_after,
                            'inventory_value' => $new * (float) $movement->average_cost_after,
                            'last_movement_at' => $movement->occurred_at,
                        ]);
                    }
                });

            Product::query()->each(function (Product $product): void {
                $balances = InventoryBalance::query()
                    ->where('organization_id', $product->organization_id)
                    ->where('product_id', $product->id)
                    ->get();

                $quantity = (float) $balances->sum('quantity_on_hand');
                $value = (float) $balances->sum('inventory_value');

                $product->updateQuietly([
                    'average_cost' => $quantity > 0 ? $value / $quantity : 0,
                ]);
            });
        });

        $this->info('Saldos de estoque reconstruídos com sucesso.');

        return self::SUCCESS;
    }
}
