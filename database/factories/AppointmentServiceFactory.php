<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AppointmentServiceFactory extends Factory
{
    protected $model = AppointmentService::class;

    public function definition(): array
    {
        return [
            'price' => $this->faker->randomNumber(),
            'duration' => $this->faker->randomNumber(),
            'quantity' => $this->faker->randomNumber(),
            'total' => $this->faker->randomNumber(),
            'notes' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'appointment_id' => Appointment::factory(),
            'service_id' => Service::factory(),
        ];
    }
}
