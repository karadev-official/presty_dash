<?php

namespace App\Policies;

use App\Models\LoyaltyProgram;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LoyaltyProgramPolicy
{
    use HandlesAuthorization;

    public function view(User $user, LoyaltyProgram $loyaltyProgram): bool
    {
        return $user->professionalProfile->id === $loyaltyProgram->professional_profile_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['pro']) && !$user->professionalProfile->loyaltyProgram()->exists();
    }

    public function update(User $user, LoyaltyProgram $loyaltyProgram): bool
    {
        return $user->professionalProfile->id === $loyaltyProgram->professional_profile_id;
    }
}
