<?php

namespace App\Services\Rentals;

use App\Models\Asset;
use App\Models\RentalReservationItem;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RentalAvailabilityService
{
    public const BLOCKING_STATUSES = [
        'pending',
        'confirmed',
        'preparing',
        'converted',
        'active',
        'in_rental',
        'rented',
    ];

    public function assertAvailable(
        string $organizationId,
        string $assetId,
        CarbonInterface|string $startsAt,
        CarbonInterface|string $endsAt,
        ?string $ignoreReservationId = null,
    ): void {
        $startsAt = \Illuminate\Support\Carbon::parse($startsAt);
        $endsAt = \Illuminate\Support\Carbon::parse($endsAt);

        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            throw ValidationException::withMessages([
                'items' => 'A devolução deve ser posterior à retirada.',
            ]);
        }

        DB::transaction(function () use (
            $organizationId,
            $assetId,
            $startsAt,
            $endsAt,
            $ignoreReservationId,
        ): void {
            Asset::query()
                ->where('organization_id', $organizationId)
                ->whereKey($assetId)
                ->lockForUpdate()
                ->firstOrFail();

            $conflict = RentalReservationItem::query()
                ->with(['reservation.customer', 'asset'])
                ->where('organization_id', $organizationId)
                ->where('asset_id', $assetId)
                ->when(
                    filled($ignoreReservationId),
                    fn (Builder $query): Builder =>
                        $query->where('reservation_id', '!=', $ignoreReservationId)
                )
                ->where('starts_at', '<', $endsAt)
                ->where('ends_at', '>', $startsAt)
                ->whereHas(
                    'reservation',
                    fn (Builder $query): Builder =>
                        $query->whereIn('status', self::BLOCKING_STATUSES)
                )
                ->orderBy('starts_at')
                ->first();

            if (! $conflict) {
                return;
            }

            $number = $conflict->reservation?->number ?: 'reserva existente';
            $prefix = $conflict->asset?->prefix ?: 'ativo';
            $from = $conflict->starts_at?->format('d/m/Y H:i') ?: '-';
            $to = $conflict->ends_at?->format('d/m/Y H:i') ?: '-';

            throw ValidationException::withMessages([
                'items' => "O ativo {$prefix} já está ocupado pela reserva {$number}, de {$from} até {$to}. Escolha outro ativo ou período.",
            ]);
        }, 3);
    }

    public function availableAssetOptions(
        string $organizationId,
        CarbonInterface|string $startsAt,
        CarbonInterface|string $endsAt,
        ?string $ignoreReservationId = null,
        ?string $search = null,
    ): array {
        $startsAt = \Illuminate\Support\Carbon::parse($startsAt);
        $endsAt = \Illuminate\Support\Carbon::parse($endsAt);

        return Asset::query()
            ->where('organization_id', $organizationId)
            ->where('status', 'active')
            ->where('operational_status', '!=', 'maintenance')
            ->where('rental_status', '!=', 'blocked')
            ->when(filled($search), function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('prefix', 'ilike', "%{$search}%")
                        ->orWhere('plate', 'ilike', "%{$search}%")
                        ->orWhere('name', 'ilike', "%{$search}%");
                });
            })
            ->whereDoesntHave('rentalReservationItems', function (Builder $query) use (
                $startsAt,
                $endsAt,
                $ignoreReservationId,
            ): void {
                $query
                    ->when(
                        filled($ignoreReservationId),
                        fn (Builder $query): Builder =>
                            $query->where('reservation_id', '!=', $ignoreReservationId)
                    )
                    ->where('starts_at', '<', $endsAt)
                    ->where('ends_at', '>', $startsAt)
                    ->whereHas(
                        'reservation',
                        fn (Builder $query): Builder =>
                            $query->whereIn('status', self::BLOCKING_STATUSES)
                    );
            })
            ->orderBy('prefix')
            ->limit(100)
            ->get()
            ->mapWithKeys(fn (Asset $asset): array => [
                $asset->id => collect([
                    $asset->prefix,
                    $asset->plate,
                    $asset->name,
                ])->filter()->implode(' - '),
            ])
            ->all();
    }
}
