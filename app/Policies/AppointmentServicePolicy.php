<?php

namespace App\Policies;

use App\Models\AppointmentService;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppointmentServicePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, AppointmentService $appointmentService): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, AppointmentService $appointmentService): bool
    {
    }

    public function delete(User $user, AppointmentService $appointmentService): bool
    {
    }

    public function restore(User $user, AppointmentService $appointmentService): bool
    {
    }

    public function forceDelete(User $user, AppointmentService $appointmentService): bool
    {
    }
}
