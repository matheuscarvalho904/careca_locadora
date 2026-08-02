<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_platform_admin || filled($user->organization_id);
    }

    public function view(User $user, Asset $asset): bool
    {
        return $user->is_platform_admin
            || $user->organization_id === $asset->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->is_platform_admin || filled($user->organization_id);
    }

    public function update(User $user, Asset $asset): bool
    {
        return $this->view($user, $asset);
    }

    public function delete(User $user, Asset $asset): bool
    {
        return $this->view($user, $asset);
    }

    public function restore(User $user, Asset $asset): bool
    {
        return $this->view($user, $asset);
    }

    public function forceDelete(User $user, Asset $asset): bool
    {
        return $user->is_platform_admin;
    }
}
