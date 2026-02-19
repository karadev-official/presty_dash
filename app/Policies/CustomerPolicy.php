<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->ProfessionalProfile->id == $customer->professional_profile_id && $user->hasRole(["customer", "pro"]);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(["pro", "super-admin"]);
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->hasRole(["pro", "super-admin"]) && $user->ProfessionalProfile->id == $customer->professional_profile_id;
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->hasRole(["pro", "super-admin"]) && $user->ProfessionalProfile->id == $customer->professional_profile_id;
    }

    public function restore(User $user, Customer $customer): bool
    {
        return $user->hasRole(["pro", "super-admin"]) && $user->ProfessionalProfile->id == $customer->professional_profile_id;
    }

    public function forceDelete(User $user, Customer $customer): bool
    {
        return $user->hasRole(["super-admin"]);
    }
}
