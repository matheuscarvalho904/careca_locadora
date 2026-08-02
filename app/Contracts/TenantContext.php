<?php

namespace App\Contracts;

use App\Models\Organization;

interface TenantContext
{
    public function id(): ?string;

    public function organization(): ?Organization;

    public function set(?Organization $organization): void;

    public function clear(): void;

    public function hasTenant(): bool;
}
