<?php

namespace App\Support;

use App\Contracts\TenantContext;
use App\Models\Organization;

final class TenantContextManager implements TenantContext
{
    private ?Organization $organization = null;

    public function id(): ?string
    {
        return $this->organization?->getKey();
    }

    public function organization(): ?Organization
    {
        return $this->organization;
    }

    public function set(?Organization $organization): void
    {
        $this->organization = $organization;
    }

    public function clear(): void
    {
        $this->organization = null;
    }

    public function hasTenant(): bool
    {
        return $this->organization !== null;
    }
}
