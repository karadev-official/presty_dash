<?php

namespace Database\Factories;

use App\Models\AppointmentService;
use App\Models\AppointmentServiceOption;
use App\Models\ServiceOption;
use App\Models\ServiceOptionGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AppointmentServiceOptionFactory extends Factory
{
    protected $model = AppointmentServiceOption::class;

    public function definition(): array
    {
        return [
            'option_name' => $this->faker->name(),
            'group_name' => $this->faker->name(),
            'price' => $this->faker->randomNumber(),
            'duration' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'appointment_service_id' => AppointmentService::factory(),
            'service_option_id' => ServiceOption::factory(),
            'service_option_group_id' => ServiceOptionGroup::factory(),
        ];
    }
}
