<?php

namespace App\Policies;

use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductCategoryPolicy{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        //
    }

    public function view(User $user, ProductCategory $productCategory): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, ProductCategory $productCategory): bool
    {
    }

    public function delete(User $user, ProductCategory $productCategory): bool
    {
    }

    public function restore(User $user, ProductCategory $productCategory): bool
    {
    }

    public function forceDelete(User $user, ProductCategory $productCategory): bool
    {
    }
}
