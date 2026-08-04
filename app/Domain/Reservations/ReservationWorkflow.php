<?php

namespace App\Domain\Reservations;

use App\Data\Rentals\ReservationSearch;
use App\Models\RentalReservation;
use App\Services\Rentals\RentalReservationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReservationWorkflow
{
    public function __construct(
        private readonly ReservationAvailabilityEngine $availability,
        private readonly ReservationPricingEngine $pricing,
        private readonly RentalReservationService $reservations,
    ) {
    }

    public function create(array $data): RentalReservation
    {
        return DB::transaction(function () use ($data): RentalReservation {
            $search = ReservationSearch::fromArray([
                'organization_id' => $data['organization_id'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'asset_id' => $data['asset_id'] ?? null,
                'starts_at' => $data['pickup_expected_at'] ?? null,
                'ends_at' => $data['return_expected_at'] ?? null,
                'preparation_minutes' =>
                    $data['preparation_minutes'] ?? 0,
            ]);

            $asset = filled($data['asset_id'] ?? null)
                ? $this->availability
                    ->availableAssets($search, limit: 1)
                    ->first()
                : app(ReservationAssignmentEngine::class)->best($search);

            if (! $asset) {
                throw ValidationException::withMessages([
                    'availability' =>
                        'Nenhum ativo disponível para o período informado.',
                ]);
            }

            $quote = $this->pricing->quote(
                startsAt: $search->startsAt,
                endsAt: $search->endsAt,
                billingUnit: (string) ($data['billing_unit'] ?? 'daily'),
                unitValue: (float) ($data['unit_value'] ?? 0),
                discountValue:
                    (float) ($data['item_discount_value'] ?? 0),
                additionalValue:
                    (float) ($data['item_additional_value'] ?? 0),
                depositValue: (float) ($data['deposit_value'] ?? 0),
            );

            $reservation = RentalReservation::query()->create([
                'organization_id' => $search->organizationId,
                'company_id' => $data['company_id'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
                'cost_center_id' => $data['cost_center_id'] ?? null,
                'business_partner_id' => $data['business_partner_id'],
                'authorized_contact_id' =>
                    $data['authorized_contact_id'] ?? null,
                'responsible_user_id' =>
                    $data['responsible_user_id'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'pickup_expected_at' => $search->startsAt,
                'return_expected_at' => $search->endsAt,
                'pickup_location' => $data['pickup_location'] ?? null,
                'return_location' => $data['return_location'] ?? null,
                'discount_value' =>
                    (float) ($data['discount_value'] ?? 0),
                'additional_value' =>
                    (float) ($data['additional_value'] ?? 0),
                'deposit_value' => $quote->depositValue,
                'commercial_notes' =>
                    $data['commercial_notes'] ?? null,
                'operational_notes' =>
                    $data['operational_notes'] ?? null,
                'metadata' => array_merge(
                    (array) ($data['metadata'] ?? []),
                    [
                        'origin' => $data['origin'] ?? 'reservation_engine',
                        'category_id_requested' => $search->categoryId,
                        'preparation_minutes' =>
                            $search->preparationMinutes,
                    ]
                ),
            ]);

            $reservation->items()->create([
                'organization_id' => $search->organizationId,
                'asset_id' => $asset->id,
                'starts_at' => $search->startsAt,
                'ends_at' => $search->endsAt,
                'billing_unit' => $quote->billingUnit,
                'quantity' => $quote->quantity,
                'unit_value' => $quote->unitValue,
                'discount_value' => $quote->discountValue,
                'additional_value' => $quote->additionalValue,
                'total_value' => $quote->totalValue,
                'metadata' => [
                    'assigned_by' => 'reservation_engine',
                    'category_id_requested' => $search->categoryId,
                ],
            ]);

            $this->reservations->recalculate($reservation->fresh());

            return $reservation->fresh([
                'customer',
                'branch',
                'items.asset.category',
            ]);
        }, 3);
    }
}
