<?php
namespace App\Policies;
use App\Models\Organization;
use App\Models\User;
class OrganizationPolicy
{
    public function before(User $user): ?bool { return $user->is_platform_admin ? true : null; }
    public function viewAny(User $user): bool { return false; }
    public function view(User $user, Organization $organization): bool { return $user->organization_id === $organization->id; }
    public function create(User $user): bool { return false; }
    public function update(User $user, Organization $organization): bool { return $user->organization_id === $organization->id; }
    public function delete(User $user, Organization $organization): bool { return false; }
    public function deleteAny(User $user): bool { return false; }
    public function restore(User $user, Organization $organization): bool { return false; }
    public function restoreAny(User $user): bool { return false; }
    public function forceDelete(User $user, Organization $organization): bool { return false; }
    public function forceDeleteAny(User $user): bool { return false; }
}
