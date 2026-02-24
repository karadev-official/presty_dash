<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\ProfessionalProfile;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'date' => Carbon::now(),
            'start_time' => Carbon::now(),
            'end_time' => Carbon::now(),
            'duration' => $this->faker->randomNumber(),
            'status' => $this->faker->words(),
            'subtotal' => $this->faker->randomNumber(),
            'discount' => $this->faker->randomNumber(),
            'total' => $this->faker->randomNumber(),
            'customer_notes' => $this->faker->word(),
            'internal_notes' => $this->faker->word(),
            'payment_status' => $this->faker->words(),
            'deposit_amount' => $this->faker->randomNumber(),
            'deposit_paid_at' => Carbon::now(),
            'deposit_payment_method' => $this->faker->word(),
            'amount_paid' => $this->faker->randomNumber(),
            'remaining_amount' => $this->faker->randomNumber(),
            'paid_at' => Carbon::now(),
            'payment_method' => $this->faker->word(),
            'cancelled_at' => Carbon::now(),
            'cancellation_reason' => $this->faker->word(),
            'reminder_sent' => $this->faker->boolean(),
            'reminder_sent_at' => Carbon::now(),
            'confirmed_at' => Carbon::now(),
            'completed_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'professional_profile_id' => ProfessionalProfile::factory(),
            'customer_id' => Customer::factory(),
            'resource_id' => Resource::factory(),
            'cancelled_by' => User::factory(),
        ];
    }
}
