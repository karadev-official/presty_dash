<?php

namespace App\Policies;

use App\Models\Workplace;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkplacePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
    }

    public function view(User $user, Workplace $workplace): bool
    {
        return $user->professionalProfile->id == $workplace->professional_profile_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['pro', 'super-admin']);
    }

    public function update(User $user, Workplace $workplace): bool
    {
        return $user->professionalProfile->id == $workplace->professional_profile_id;
    }

    public function delete(User $user, Workplace $workplace): bool
    {
        return $user->professionalProfile->id == $workplace->professional_profile_id;
    }

    public function restore(User $user, Workplace $workplace): bool
    {
        return false;
    }

    public function forceDelete(User $user, Workplace $workplace): bool
    {
        return false;
    }
}
