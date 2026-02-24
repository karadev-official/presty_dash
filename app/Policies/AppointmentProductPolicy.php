<?php

namespace App\Policies;

use App\Models\AppointmentProduct;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppointmentProductPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, AppointmentProduct $appointmentProduct): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, AppointmentProduct $appointmentProduct): bool
    {
    }

    public function delete(User $user, AppointmentProduct $appointmentProduct): bool
    {
    }

    public function restore(User $user, AppointmentProduct $appointmentProduct): bool
    {
    }

    public function forceDelete(User $user, AppointmentProduct $appointmentProduct): bool
    {
    }
}
