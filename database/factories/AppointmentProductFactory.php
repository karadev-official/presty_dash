<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\AppointmentProduct;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AppointmentProductFactory extends Factory
{
    protected $model = AppointmentProduct::class;

    public function definition(): array
    {
        return [
            'price' => $this->faker->randomNumber(),
            'quantity' => $this->faker->randomNumber(),
            'total' => $this->faker->randomNumber(),
            'notes' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'appointment_id' => Appointment::factory(),
            'product_id' => Product::factory(),
        ];
    }
}
