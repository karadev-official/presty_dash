<?php

namespace App\Policies;

use App\Models\ProfessionalWorkplace;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class workplacePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
    }

    public function view(User $user, ProfessionalWorkplace $workplace): bool
    {
        return $user->professionalProfile->id == $workplace->professional_profile_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['pro', 'super-admin']);
    }

    public function update(User $user, ProfessionalWorkplace $workplace): bool
    {
        return $user->professionalProfile->id == $workplace->professional_profile_id;
    }

    public function delete(User $user, ProfessionalWorkplace $workplace): bool
    {
        return $user->professionalProfile->id == $workplace->professional_profile_id;
    }

    public function restore(User $user, ProfessionalWorkplace $workplace): bool
    {
        return false;
    }

    public function forceDelete(User $user, ProfessionalWorkplace $workplace): bool
    {
        return false;
    }
}
