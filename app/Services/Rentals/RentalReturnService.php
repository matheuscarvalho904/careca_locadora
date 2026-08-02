<?php

namespace App\Services\Rentals;

use App\Models\RentalContract;
use App\Models\RentalReturn;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RentalReturnService
{
    public function createFromContract(RentalContract $contract): RentalReturn
    {
        return DB::transaction(function () use ($contract): RentalReturn {
            $contract->loadMissing(['delivery.items.asset', 'customer']);

            if ($contract->status !== 'active') {
                throw ValidationException::withMessages([
                    'status' => 'Somente contratos ativos podem iniciar uma devolução.',
                ]);
            }

            if ($contract->delivery === null || $contract->delivery->status !== 'completed') {
                throw ValidationException::withMessages([
                    'delivery' => 'A entrega precisa estar concluída antes da devolução.',
                ]);
            }

            $existing = RentalReturn::query()
                ->where('contract_id', $contract->id)
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            $return = RentalReturn::query()->create([
                'organization_id' => $contract->organization_id,
                'contract_id' => $contract->id,
                'delivery_id' => $contract->delivery->id,
                'responsible_user_id' => auth()->id(),
                'status' => 'draft',
                'scheduled_at' => $contract->ends_at,
            ]);

            foreach ($contract->delivery->items as $deliveryItem) {
                $return->items()->create([
                    'organization_id' => $contract->organization_id,
                    'delivery_item_id' => $deliveryItem->id,
                    'contract_item_id' => $deliveryItem->contract_item_id,
                    'asset_id' => $deliveryItem->asset_id,
                    'initial_odometer' => $deliveryItem->odometer,
                    'initial_hourmeter' => $deliveryItem->hourmeter,
                    'initial_fuel_level' => $deliveryItem->fuel_level,
                    'body_ok' => $deliveryItem->body_ok,
                    'tires_ok' => $deliveryItem->tires_ok,
                    'lights_ok' => $deliveryItem->lights_ok,
                    'glass_ok' => $deliveryItem->glass_ok,
                    'documents_ok' => $deliveryItem->documents_ok,
                    'accessories_ok' => $deliveryItem->accessories_ok,
                    'cleanliness_ok' => $deliveryItem->cleanliness_ok,
                    'primary_key_returned' => $deliveryItem->primary_key_delivered,
                    'spare_key_returned' => $deliveryItem->spare_key_delivered,
                    'manual_returned' => $deliveryItem->manual_delivered,
                ]);
            }

            return $return->fresh(['items.asset', 'contract.customer', 'delivery']);
        });
    }

    public function recalculate(RentalReturn $return): RentalReturn
    {
        return DB::transaction(function () use ($return): RentalReturn {
            $totals = [
                'extra_time_value' => 0,
                'mileage_value' => 0,
                'fuel_value' => 0,
                'damage_value' => 0,
                'cleaning_value' => 0,
                'missing_accessories_value' => 0,
                'other_value' => 0,
            ];

            foreach ($return->items()->get() as $item) {
                foreach (array_keys($totals) as $field) {
                    $totals[$field] += (float) $item->{$field};
                }
            }

            $return->forceFill($totals)->save();

            return $return->fresh(['items.asset', 'contract.customer']);
        });
    }

    public function complete(RentalReturn $return): RentalReturn
    {
        return DB::transaction(function () use ($return): RentalReturn {
            $return->loadMissing([
                'items.asset',
                'items.contractItem',
                'contract.reservation',
            ]);

            if ($return->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'A devolução precisa possuir ao menos um ativo.',
                ]);
            }

            if (blank($return->customer_signer_name)) {
                throw ValidationException::withMessages([
                    'customer_signer_name' => 'Informe quem devolveu os ativos.',
                ]);
            }

            foreach ($return->items as $item) {
                if ($item->initial_odometer !== null && $item->final_odometer === null) {
                    throw ValidationException::withMessages([
                        'items' => "Informe o hodômetro final do ativo {$item->asset?->prefix}.",
                    ]);
                }

                if ($item->initial_hourmeter !== null && $item->final_hourmeter === null) {
                    throw ValidationException::withMessages([
                        'items' => "Informe o horímetro final do ativo {$item->asset?->prefix}.",
                    ]);
                }

                if (
                    $item->initial_odometer !== null
                    && $item->final_odometer < $item->initial_odometer
                ) {
                    throw ValidationException::withMessages([
                        'items' => "O hodômetro final do ativo {$item->asset?->prefix} não pode ser menor que o inicial.",
                    ]);
                }

                if (
                    $item->initial_hourmeter !== null
                    && $item->final_hourmeter < $item->initial_hourmeter
                ) {
                    throw ValidationException::withMessages([
                        'items' => "O horímetro final do ativo {$item->asset?->prefix} não pode ser menor que o inicial.",
                    ]);
                }

                $item->save();

                $item->contractItem?->update([
                    'final_odometer' => $item->final_odometer,
                    'final_hourmeter' => $item->final_hourmeter,
                ]);

                $item->asset?->update([
                    'rental_status' => 'available',
                ]);
            }

            $return = $this->recalculate($return);

            $return->update([
                'status' => 'completed',
                'returned_at' => now(),
                'employee_signer_name' => $return->employee_signer_name
                    ?: auth()->user()?->name,
            ]);

            $return->contract->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);

            $return->contract->reservation?->update([
                'status' => 'completed',
            ]);

            return $return->fresh([
                'items.asset',
                'contract.customer',
                'contract.reservation',
            ]);
        });
    }
}
