<?php

namespace App\Observers;

use App\Models\Company;

class CompanyObserver
{
    public function creating(Company $company): void
    {
        $company->organization_id ??= auth()->user()?->organization_id;
    }
}
