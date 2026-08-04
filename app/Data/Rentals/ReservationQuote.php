<?php

namespace App\Data\Rentals;

final readonly class ReservationQuote
{
    public function __construct(
        public string $billingUnit,
        public float $quantity,
        public float $unitValue,
        public float $subtotal,
        public float $discountValue,
        public float $additionalValue,
        public float $depositValue,
        public float $totalValue,
        public array $breakdown,
    ) {
    }

    public function toArray(): array
    {
        return [
            'billing_unit' => $this->billingUnit,
            'quantity' => $this->quantity,
            'unit_value' => $this->unitValue,
            'subtotal' => $this->subtotal,
            'discount_value' => $this->discountValue,
            'additional_value' => $this->additionalValue,
            'deposit_value' => $this->depositValue,
            'total_value' => $this->totalValue,
            'breakdown' => $this->breakdown,
        ];
    }
}
