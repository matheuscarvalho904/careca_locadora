<?php

namespace App\Policies;

use App\Models\BusinessPartner;
use App\Models\User;

class BusinessPartnerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_platform_admin || filled($user->organization_id);
    }

    public function view(User $user, BusinessPartner $partner): bool
    {
        return $user->is_platform_admin
            || $user->organization_id === $partner->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->is_platform_admin || filled($user->organization_id);
    }

    public function update(User $user, BusinessPartner $partner): bool
    {
        return $this->view($user, $partner);
    }

    public function delete(User $user, BusinessPartner $partner): bool
    {
        return $this->view($user, $partner);
    }

    public function restore(User $user, BusinessPartner $partner): bool
    {
        return $this->view($user, $partner);
    }

    public function forceDelete(User $user, BusinessPartner $partner): bool
    {
        return $user->is_platform_admin;
    }
}
