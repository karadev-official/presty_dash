<?php

namespace App\Policies;

use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductCategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
//        return true;
    }

    public function view(User $user, ProductCategory $category): bool
    {
        return $user->professionalProfile->id === $category->professional_profile_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['pro']) && $user->professionalProfile()->exists();
    }

    public function update(User $user, ProductCategory $category): bool
    {
        return $user->professionalProfile->id === $category->professional_profile_id;
    }

    public function delete(User $user, ProductCategory $category): bool
    {
        return $user->professionalProfile->id === $category->professional_profile_id;
    }

    public function restore(User $user, ProductCategory $category): bool
    {
        return $user->professionalProfile->id === $category->professional_profile_id;
    }

    public function forceDelete(User $user, ProductCategory $category): bool
    {
        return $user->professionalProfile->id === $category->professional_profile_id;
    }
}
