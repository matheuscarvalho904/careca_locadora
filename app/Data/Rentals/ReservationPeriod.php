<?php

namespace App\Data\Rentals;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

final readonly class ReservationPeriod
{
    public CarbonImmutable $startsAt;
    public CarbonImmutable $endsAt;

    public function __construct(
        CarbonImmutable|string $startsAt,
        CarbonImmutable|string $endsAt,
    ) {
        $this->startsAt = $startsAt instanceof CarbonImmutable
            ? $startsAt
            : CarbonImmutable::parse($startsAt);

        $this->endsAt = $endsAt instanceof CarbonImmutable
            ? $endsAt
            : CarbonImmutable::parse($endsAt);

        if ($this->endsAt->lessThanOrEqualTo($this->startsAt)) {
            throw new InvalidArgumentException(
                'A devolução deve ser posterior à retirada.'
            );
        }
    }

    public function overlaps(self $other): bool
    {
        return $this->startsAt->lessThan($other->endsAt)
            && $this->endsAt->greaterThan($other->startsAt);
    }

    public function hours(): int
    {
        return max(1, (int) ceil(
            $this->startsAt->diffInMinutes($this->endsAt) / 60
        ));
    }

    public function days(): int
    {
        return max(1, (int) ceil($this->hours() / 24));
    }
}
