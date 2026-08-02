<?php

namespace App\Services\Rentals;

use App\Models\RentalContract;
use App\Models\RentalReservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReservationToContractService
{
    public function convert(RentalReservation $reservation): RentalContract
    {
        return DB::transaction(function () use ($reservation): RentalContract {
            $reservation->loadMissing('items');

            if ($reservation->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'status' => 'Uma reserva cancelada não pode ser convertida.',
                ]);
            }

            if ($reservation->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'A reserva precisa possuir ao menos um ativo.',
                ]);
            }

            $existing = RentalContract::query()
                ->where('reservation_id', $reservation->id)
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            $contract = RentalContract::query()->create([
                'organization_id' => $reservation->organization_id,
                'reservation_id' => $reservation->id,
                'company_id' => $reservation->company_id,
                'branch_id' => $reservation->branch_id,
                'cost_center_id' => $reservation->cost_center_id,
                'business_partner_id' => $reservation->business_partner_id,
                'authorized_contact_id' => $reservation->authorized_contact_id,
                'responsible_user_id' => $reservation->responsible_user_id,
                'status' => 'draft',
                'starts_at' => $reservation->pickup_expected_at,
                'ends_at' => $reservation->return_expected_at,
                'pickup_location' => $reservation->pickup_location,
                'return_location' => $reservation->return_location,
                'subtotal' => $reservation->subtotal,
                'discount_value' => $reservation->discount_value,
                'additional_value' => $reservation->additional_value,
                'deposit_value' => $reservation->deposit_value,
                'total_value' => $reservation->total_value,
                'commercial_notes' => $reservation->commercial_notes,
                'operational_notes' => $reservation->operational_notes,
            ]);

            foreach ($reservation->items as $item) {
                $contract->items()->create([
                    'organization_id' => $reservation->organization_id,
                    'asset_id' => $item->asset_id,
                    'starts_at' => $item->starts_at,
                    'ends_at' => $item->ends_at,
                    'billing_unit' => $item->billing_unit,
                    'quantity' => $item->quantity,
                    'unit_value' => $item->unit_value,
                    'discount_value' => $item->discount_value,
                    'additional_value' => $item->additional_value,
                    'total_value' => $item->total_value,
                    'initial_odometer' => $item->expected_initial_odometer,
                    'initial_hourmeter' => $item->expected_initial_hourmeter,
                    'notes' => $item->notes,
                ]);
            }

            $reservation->update([
                'status' => 'converted',
            ]);

            $reservation->items()
                ->with('asset')
                ->get()
                ->each(fn ($item) => $item->asset?->update([
                    'rental_status' => 'rented',
                ]));

            return $contract->fresh(['items', 'customer']);
        });
    }
}
