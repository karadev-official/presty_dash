<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AddressFactory extends Factory{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'street' => $this->faker->streetName(),//
'city' => $this->faker->city(),
'postal_code' => $this->faker->postcode(),
'country' => $this->faker->country(),
'additional_info' => $this->faker->word(),
'created_at' => Carbon::now(),
'updated_at' => Carbon::now(),
        ];
    }
}
