<?php

namespace App\Policies;

use App\Models\LoyaltyReward;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LoyaltyRewardPolicy
{
    use HandlesAuthorization;

    public function delete(User $user, LoyaltyReward $loyaltyReward): bool
    {
        return $user->professionalProfile->loyaltyProgram->id === $loyaltyReward->loyalty_program_id;
    }
}
