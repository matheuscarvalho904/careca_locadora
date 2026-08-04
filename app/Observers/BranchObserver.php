<?php

namespace App\Observers;

use App\Models\Branch;

class BranchObserver
{
    public function creating(Branch $branch): void
    {
        $branch->organization_id ??= auth()->user()?->organization_id;
    }
}
