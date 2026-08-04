<?php

namespace App\Data\Rentals;

use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

final readonly class ReservationSearch
{
    public function __construct(
        public string $organizationId,
        public Carbon $startsAt,
        public Carbon $endsAt,
        public ?string $branchId = null,
        public ?string $categoryId = null,
        public ?string $assetId = null,
        public ?string $ignoreReservationId = null,
        public int $preparationMinutes = 0,
    ) {
        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            throw ValidationException::withMessages([
                'period' => 'A devolução deve ser posterior à retirada.',
            ]);
        }

        if ($preparationMinutes < 0 || $preparationMinutes > 4320) {
            throw ValidationException::withMessages([
                'preparation_minutes' =>
                    'O tempo de preparação deve estar entre 0 e 4320 minutos.',
            ]);
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            organizationId: (string) ($data['organization_id'] ?? ''),
            startsAt: Carbon::parse($data['starts_at'] ?? null),
            endsAt: Carbon::parse($data['ends_at'] ?? null),
            branchId: filled($data['branch_id'] ?? null)
                ? (string) $data['branch_id']
                : null,
            categoryId: filled($data['category_id'] ?? null)
                ? (string) $data['category_id']
                : null,
            assetId: filled($data['asset_id'] ?? null)
                ? (string) $data['asset_id']
                : null,
            ignoreReservationId:
                filled($data['ignore_reservation_id'] ?? null)
                    ? (string) $data['ignore_reservation_id']
                    : null,
            preparationMinutes:
                max(0, (int) ($data['preparation_minutes'] ?? 0)),
        );
    }

    public function effectiveStartsAt(): Carbon
    {
        return $this->startsAt->copy()
            ->subMinutes($this->preparationMinutes);
    }

    public function effectiveEndsAt(): Carbon
    {
        return $this->endsAt->copy()
            ->addMinutes($this->preparationMinutes);
    }
}
