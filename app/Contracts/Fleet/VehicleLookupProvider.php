<?php

namespace App\Contracts\Fleet;

use App\Data\Fleet\VehicleLookupResult;

interface VehicleLookupProvider
{
    public function lookup(string $plate): VehicleLookupResult;

    public function name(): string;
}
