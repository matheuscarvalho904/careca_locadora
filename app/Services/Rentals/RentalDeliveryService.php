<?php

namespace App\Services\Rentals;

use App\Models\RentalContract;
use App\Models\RentalDelivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RentalDeliveryService
{
    public function createFromContract(RentalContract $contract): RentalDelivery
    {
        return DB::transaction(function () use ($contract): RentalDelivery {
            $contract->loadMissing(['items.asset']);

            if (! in_array($contract->status, ['active'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Somente contratos ativos podem iniciar uma entrega.',
                ]);
            }

            if ($contract->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'O contrato não possui ativos para entrega.',
                ]);
            }

            $existing = RentalDelivery::query()
                ->where('contract_id', $contract->id)
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            $delivery = RentalDelivery::query()->create([
                'organization_id' => $contract->organization_id,
                'contract_id' => $contract->id,
                'responsible_user_id' => auth()->id(),
                'status' => 'draft',
                'scheduled_at' => $contract->starts_at,
            ]);

            foreach ($contract->items as $contractItem) {
                $delivery->items()->create([
                    'organization_id' => $contract->organization_id,
                    'contract_item_id' => $contractItem->id,
                    'asset_id' => $contractItem->asset_id,
                    'odometer' => $contractItem->initial_odometer,
                    'hourmeter' => $contractItem->initial_hourmeter,
                ]);
            }

            return $delivery->fresh(['items.asset', 'contract.customer']);
        });
    }

    public function complete(RentalDelivery $delivery): RentalDelivery
    {
        return DB::transaction(function () use ($delivery): RentalDelivery {
            $delivery->loadMissing(['items.asset', 'contract']);

            if ($delivery->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'A entrega precisa possuir ao menos um ativo.',
                ]);
            }

            if (blank($delivery->customer_signer_name)) {
                throw ValidationException::withMessages([
                    'customer_signer_name' => 'Informe quem recebeu os ativos.',
                ]);
            }

            foreach ($delivery->items as $item) {
                if ($item->odometer === null && $item->hourmeter === null) {
                    throw ValidationException::withMessages([
                        'items' => "Informe o hodômetro ou horímetro do ativo {$item->asset?->prefix}.",
                    ]);
                }

                $item->contractItem?->update([
                    'initial_odometer' => $item->odometer,
                    'initial_hourmeter' => $item->hourmeter,
                ]);

                $item->asset?->update([
                    'rental_status' => 'rented',
                ]);
            }

            $delivery->update([
                'status' => 'completed',
                'delivered_at' => now(),
                'employee_signer_name' => $delivery->employee_signer_name
                    ?: auth()->user()?->name,
            ]);

            return $delivery->fresh(['items.asset', 'contract.customer']);
        });
    }
}
