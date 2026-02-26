<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppointmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $user->professionalProfile->id === $appointment->professional_profile_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['pro', 'super-admin',]);
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->professionalProfile->id == $appointment->professional_profile_id;
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->professionalProfile->id == $appointment->professional_profile_id;
    }

    public function restore(User $user, Appointment $appointment): bool
    {
        return false;
    }

    public function forceDelete(User $user, Appointment $appointment): bool
    {
        return false;
    }
}
