<?php

namespace App\Filament\Support;

use App\Models\ApplicationCenter;
use App\Models\Asset;
use App\Models\CostCenter;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;

final class ProcurementSelectOptions
{
    public static function assetLabel(Asset $asset): string
    {
        return collect([
            $asset->prefix,
            $asset->plate,
            $asset->name,
        ])->filter()->implode(' - ');
    }

    public static function assetResults(string $search): array
    {
        return Asset::query()
            ->where('status', 'active')
            ->where(function (Builder $query) use ($search): void {
                $query
                    ->where('prefix', 'ilike', "%{$search}%")
                    ->orWhere('plate', 'ilike', "%{$search}%")
                    ->orWhere('name', 'ilike', "%{$search}%");
            })
            ->orderBy('prefix')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (Asset $asset): array => [
                $asset->id => self::assetLabel($asset),
            ])
            ->all();
    }

    public static function productLabel(Product $product): string
    {
        return collect([
            $product->code,
            $product->name,
            $product->brand?->name,
        ])->filter()->implode(' - ');
    }

    public static function productResults(string $search): array
    {
        return Product::query()
            ->with('brand')
            ->where('status', 'active')
            ->where(function (Builder $query) use ($search): void {
                $query
                    ->where('code', 'ilike', "%{$search}%")
                    ->orWhere('sku', 'ilike', "%{$search}%")
                    ->orWhere('barcode', 'ilike', "%{$search}%")
                    ->orWhere('name', 'ilike', "%{$search}%")
                    ->orWhereHas('brand', fn (Builder $brandQuery) =>
                        $brandQuery->where('name', 'ilike', "%{$search}%")
                    );
            })
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (Product $product): array => [
                $product->id => self::productLabel($product),
            ])
            ->all();
    }

    public static function applicationCenterLabel(ApplicationCenter $center): string
    {
        return collect([$center->code, $center->name])
            ->filter()
            ->implode(' - ');
    }

    public static function costCenterLabel(CostCenter $center): string
    {
        return collect([$center->code, $center->name])
            ->filter()
            ->implode(' - ');
    }

    public static function warehouseLabel(Warehouse $warehouse): string
    {
        return collect([$warehouse->code, $warehouse->name])
            ->filter()
            ->implode(' - ');
    }
}
