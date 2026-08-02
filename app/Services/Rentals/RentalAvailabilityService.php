<?php

namespace App\Services\Rentals;

use App\Data\Rentals\ReservationPeriod;
use App\Models\Asset;
use App\Models\RentalReservationItem;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

final class RentalAvailabilityService
{
    /**
     * @return array{available:bool,reason:?string}
     */
    public function check(
        string $organizationId,
        string $assetId,
        CarbonInterface|string $startsAt,
        CarbonInterface|string $endsAt,
        ?string $ignoreReservationId = null,
    ): array {
        $period = new ReservationPeriod(
            (string) $startsAt,
            (string) $endsAt,
        );

        $asset = Asset::query()
            ->withoutOrganizationScope()
            ->where('organization_id', $organizationId)
            ->find($assetId);

        if ($asset === null) {
            return [
                'available' => false,
                'reason' => 'Ativo não encontrado nesta organização.',
            ];
        }

        if ($asset->operational_status !== 'available') {
            return [
                'available' => false,
                'reason' => 'O ativo não está operacionalmente disponível.',
            ];
        }

        if ($asset->rental_status === 'blocked') {
            return [
                'available' => false,
                'reason' => 'O ativo está bloqueado para locação.',
            ];
        }

        $conflict = RentalReservationItem::query()
            ->where('organization_id', $organizationId)
            ->where('asset_id', $assetId)
            ->whereHas('reservation', function ($query) use ($ignoreReservationId): void {
                $query->whereIn('status', [
                    'pending',
                    'confirmed',
                    'preparing',
                    'converted',
                ]);

                if (filled($ignoreReservationId)) {
                    $query->whereKeyNot($ignoreReservationId);
                }
            })
            ->where('starts_at', '<', $period->endsAt)
            ->where('ends_at', '>', $period->startsAt)
            ->exists();

        return $conflict
            ? [
                'available' => false,
                'reason' => 'O ativo já possui reserva para o período informado.',
            ]
            : [
                'available' => true,
                'reason' => null,
            ];
    }

    public function assertAvailable(
        string $organizationId,
        string $assetId,
        CarbonInterface|string $startsAt,
        CarbonInterface|string $endsAt,
        ?string $ignoreReservationId = null,
    ): void {
        $result = $this->check(
            organizationId: $organizationId,
            assetId: $assetId,
            startsAt: $startsAt,
            endsAt: $endsAt,
            ignoreReservationId: $ignoreReservationId,
        );

        if (! $result['available']) {
            throw ValidationException::withMessages([
                'asset_id' => $result['reason'],
            ]);
        }
    }
}
