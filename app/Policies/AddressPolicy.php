<?php

namespace App\Policies;

use App\Models\Address;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AddressPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Address $address): bool
    {
        if($user->address_id && $user->address_id === $address->id){
            return true;
        }
        $profile = $user->professionalProfile;
        if($profile){
            return $profile->workplaces()->where('address_id', $address->id)->exists();
        }
        return true;
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, Address $address): bool
    {
        if($user->address_id && $user->address_id === $address->id){
            return true;
        }
        $profile = $user->professionalProfile;
        if($profile){
            return $profile->workplaces()->where('address_id', $address->id)->exists();
        }
        return true;
    }

    public function delete(User $user, Address $address): bool
    {
        if($user->address_id && $user->address_id === $address->id){
            return true;
        }
        $profile = $user->professionalProfile;
        if($profile){
            return $profile->workplaces()->where('address_id', $address->id)->exists();
        }
        return false;
    }

    public function restore(User $user, Address $address): bool
    {
    }

    public function forceDelete(User $user, Address $address): bool
    {
    }
}
