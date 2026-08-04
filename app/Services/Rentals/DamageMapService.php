<?php

namespace App\Services\Rentals;

use App\Models\InspectionDiagramTemplate;
use App\Models\RentalDamageMark;
use App\Models\RentalDeliveryItem;
use App\Models\RentalReturnItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DamageMapService
{
    public function templateFor(
        RentalDeliveryItem|RentalReturnItem $item,
    ): ?InspectionDiagramTemplate {
        $asset = $item->asset;

        if (! $asset) {
            return null;
        }

        return InspectionDiagramTemplate::query()
            ->with([
                'views' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('display_order'),
            ])
            ->where('organization_id', $asset->organization_id)
            ->where('status', 'active')
            ->where(function ($query) use ($asset): void {
                $query
                    ->where('asset_category_id', $asset->category_id)
                    ->orWhere(function ($query) use ($asset): void {
                        $query
                            ->whereNull('asset_category_id')
                            ->where('asset_type', $asset->asset_type);
                    });
            })
            ->orderByRaw(
                'CASE WHEN asset_category_id IS NOT NULL THEN 0 ELSE 1 END'
            )
            ->orderByDesc('is_default')
            ->first();
    }

    public function deliveryMarksForReturn(
        RentalReturnItem $returnItem,
    ): Collection {
        $deliveryItem = $returnItem->deliveryItem;

        if (! $deliveryItem) {
            return collect();
        }

        return $deliveryItem
            ->damageMarks()
            ->with([
                'templateView',
                'photos',
            ])
            ->where('status', 'active')
            ->orderBy('created_at')
            ->get();
    }

    public function createMark(
        RentalDeliveryItem|RentalReturnItem $item,
        array $data,
        string $condition,
    ): RentalDamageMark {
        $this->validateCoordinates($data);

        $allowedConditions = $item instanceof RentalDeliveryItem
            ? ['preexisting']
            : ['new', 'aggravated', 'repaired'];

        if (! in_array($condition, $allowedConditions, true)) {
            throw ValidationException::withMessages([
                'condition' => 'Condição de avaria inválida para esta vistoria.',
            ]);
        }

        $template = $this->templateFor($item);

        if (! $template) {
            throw ValidationException::withMessages([
                'template' => 'Nenhum diagrama de inspeção foi configurado para este ativo.',
            ]);
        }

        $view = $template->views
            ->firstWhere('id', $data['template_view_id'] ?? null);

        if (! $view) {
            throw ValidationException::withMessages([
                'template_view_id' => 'A vista informada não pertence ao diagrama deste ativo.',
            ]);
        }

        return DB::transaction(function () use (
            $item,
            $data,
            $condition,
        ): RentalDamageMark {
            $mark = $item->damageMarks()->create([
                'organization_id' => $item->organization_id,
                'asset_id' => $item->asset_id,
                'template_view_id' => $data['template_view_id'],
                'source_damage_mark_id' => $data['source_damage_mark_id'] ?? null,
                'created_by' => auth()->id(),
                'position_x' => round((float) $data['position_x'], 4),
                'position_y' => round((float) $data['position_y'], 4),
                'vehicle_part' => filled($data['vehicle_part'] ?? null)
                    ? trim((string) $data['vehicle_part'])
                    : null,
                'damage_type' => $data['damage_type'],
                'severity' => $data['severity'] ?? 'light',
                'condition' => $condition,
                'status' => 'active',
                'estimated_value' => max(
                    0,
                    (float) ($data['estimated_value'] ?? 0)
                ),
                'description' => filled($data['description'] ?? null)
                    ? trim((string) $data['description'])
                    : null,
                'metadata' => [
                    'registered_at' => now()->toIso8601String(),
                    'origin' => $item instanceof RentalDeliveryItem
                        ? 'rental_delivery'
                        : 'rental_return',
                ],
            ]);

            if ($item instanceof RentalReturnItem) {
                $this->synchronizeReturnDamageValue($item);
            }

            return $mark->fresh([
                'templateView',
                'photos',
            ]);
        });
    }

    public function deleteMark(
        RentalDamageMark $mark,
    ): void {
        DB::transaction(function () use ($mark): void {
            $inspectable = $mark->inspectable;

            $mark->delete();

            if ($inspectable instanceof RentalReturnItem) {
                $this->synchronizeReturnDamageValue($inspectable);
            }
        });
    }

    public function synchronizeReturnDamageValue(
        RentalReturnItem $item,
    ): void {
        $value = (float) $item
            ->damageMarks()
            ->where('status', 'active')
            ->whereIn('condition', ['new', 'aggravated'])
            ->sum('estimated_value');

        $item->forceFill([
            'damage_value' => $value,
        ])->save();

        $rentalReturn = $item->rentalReturn;

        if ($rentalReturn) {
            app(RentalReturnService::class)
                ->recalculate($rentalReturn->fresh());
        }
    }

    private function validateCoordinates(
        array $data,
    ): void {
        $x = (float) ($data['position_x'] ?? -1);
        $y = (float) ($data['position_y'] ?? -1);

        if (
            $x < 0
            || $x > 100
            || $y < 0
            || $y > 100
        ) {
            throw ValidationException::withMessages([
                'position' => 'A posição da avaria deve estar entre 0% e 100%.',
            ]);
        }
    }
}
