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
        return true;
    }

    public function view(User $user, ProductCategory $category): bool
    {
        return $category->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ProductCategory $category): bool
    {
        return $category->user_id === $user->id;
    }

    public function delete(User $user, ProductCategory $category): bool
    {
        return $category->user_id === $user->id;
    }

    public function restore(User $user, ProductCategory $category): bool
    {
        //
    }

    public function forceDelete(User $user, ProductCategory $category): bool
    {
    }
}
