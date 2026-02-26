<?php

namespace App\Policies;

use App\Models\AppointmentServiceOption;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppointmentServiceOptionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, AppointmentServiceOption $appointmentServiceOption): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, AppointmentServiceOption $appointmentServiceOption): bool
    {
    }

    public function delete(User $user, AppointmentServiceOption $appointmentServiceOption): bool
    {
    }

    public function restore(User $user, AppointmentServiceOption $appointmentServiceOption): bool
    {
    }

    public function forceDelete(User $user, AppointmentServiceOption $appointmentServiceOption): bool
    {
    }
}
