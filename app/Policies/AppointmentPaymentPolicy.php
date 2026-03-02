<?php

namespace App\Policies;

use App\Models\AppointmentPayment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppointmentPaymentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, AppointmentPayment $appointmentPayment): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, AppointmentPayment $appointmentPayment): bool
    {
    }

    public function delete(User $user, AppointmentPayment $appointmentPayment): bool
    {
    }

    public function restore(User $user, AppointmentPayment $appointmentPayment): bool
    {
    }

    public function forceDelete(User $user, AppointmentPayment $appointmentPayment): bool
    {
    }
}
