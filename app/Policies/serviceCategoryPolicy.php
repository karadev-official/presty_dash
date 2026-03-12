<?php

namespace App\Policies;

use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class serviceCategoryPolicy
{
    use HandlesAuthorization;

    public function create(User $user): bool
    {
        return $user->hasRole('pro') && $user->professionalProfile()->exists();
    }
    public function view(User $user, ServiceCategory $category): bool
    {
        return $user->professionalProfile->id === $category->professional_profile_id;
    }

    public function update(User $user, ServiceCategory $category): bool
    {
        return $user->professionalProfile->id === $category->professional_profile_id;
    }

    public function delete(User $user, ServiceCategory $category): bool
    {
        return $user->professionalProfile->id === $category->professional_profile_id;
    }

    public function restore(User $user, ServiceCategory $category): bool
    {
        return $user->professionalProfile->id === $category->professional_profile_id;
    }

    public function forceDelete(User $user, ServiceCategory $category): bool
    {
        return $user->professionalProfile->id === $category->professional_profile_id;
    }
}
