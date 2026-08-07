<?php

namespace App\Services\Rentals;

use App\Data\Rentals\ReservationSearch;
use App\Domain\Reservations\ReservationWorkflow;
use App\Models\Branch;
use App\Models\BusinessPartner;
use App\Models\RentalReservation;
use Illuminate\Support\Facades\DB;

final class PublicReservationService
{
    public function __construct(
        private readonly RentalCommercialPricingService $pricing,
        private readonly ReservationWorkflow $workflow,
    ) {
    }

    public function create(
        string $organizationId,
        array $data,
    ): RentalReservation {
        return DB::transaction(function () use (
            $organizationId,
            $data
        ): RentalReservation {
            $customer = $this->resolveCustomer(
                organizationId: $organizationId,
                data: $data['customer'],
            );

            $search = ReservationSearch::fromArray([
                'organization_id' => $organizationId,
                'branch_id' => $data['branch_id'] ?? null,
                'category_id' => $data['category_id'],
                'asset_id' => $data['asset_id'],
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'],
                'preparation_minutes' => config(
                    'careca-public.preparation_minutes',
                    60
                ),
            ]);

            $quote = $this->pricing->quote(
                search: $search,
                itemIds:
                    $data['commercial_item_ids'] ?? [],
                couponCode: $data['coupon_code'] ?? null,
            );

            $branch = filled($data['branch_id'] ?? null)
                ? Branch::query()
                    ->where('organization_id', $organizationId)
                    ->find($data['branch_id'])
                : null;

            return $this->workflow->create([
                'organization_id' => $organizationId,
                'company_id' => $branch?->company_id,
                'branch_id' => $branch?->id,
                'business_partner_id' => $customer->id,
                'category_id' => $data['category_id'],
                'asset_id' => $data['asset_id'],
                'pickup_expected_at' => $data['starts_at'],
                'return_expected_at' => $data['ends_at'],
                'billing_unit' =>
                    $quote['rate_plan']['billing_unit'],
                'unit_value' =>
                    $quote['rate_plan']['unit_value'],
                'item_additional_value' =>
                    round((float) collect($quote['commercial_items'] ?? [])->sum('total'), 2),
                'item_discount_value' =>
                    ($quote['coupon_discount'] ?? 0),
                'deposit_value' =>
                    ($quote['deposit_value'] ?? 0),
                'status' => 'pending',
                'origin' => 'public_website',
                'commercial_notes' =>
                    'Reserva solicitada pelo site público.',
                'metadata' => [
                    'source' => 'website',
                    'coupon' => $quote['coupon'] ?? null,
                    'commercial_items' =>
                        $quote['commercial_items'],
                    'customer_email' => $data['customer']['email'],
                    'customer_phone' => $data['customer']['phone'],
                ],
            ]);
        }, 3);
    }

    private function resolveCustomer(
        string $organizationId,
        array $data,
    ): BusinessPartner {
        $document = preg_replace(
            '/\D+/',
            '',
            (string) $data['document']
        );

        $customer = BusinessPartner::query()
            ->withoutOrganizationScope()
            ->where('organization_id', $organizationId)
            ->where('document', $document)
            ->first();

        if (! $customer) {
            $customer = BusinessPartner::query()
                ->withoutOrganizationScope()
                ->create([
                    'organization_id' => $organizationId,
                    'roles' => ['customer'],
                    'person_type' =>
                        strlen($document) === 14
                            ? 'legal'
                            : 'individual',
                    'legal_name' => $data['name'],
                    'trade_name' => $data['name'],
                    'document' => $document,
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'status' => 'active',
                ]);

            return $customer;
        }

        $roles = collect($customer->roles ?? [])
            ->push('customer')
            ->unique()
            ->values()
            ->all();

        $customer->update([
            'roles' => $roles,
            'legal_name' =>
                $customer->legal_name ?: $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);

        return $customer->fresh();
    }
}
