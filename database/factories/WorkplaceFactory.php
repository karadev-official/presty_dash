<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\ProfessionalProfile;
use App\Models\Workplace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class WorkplaceFactory extends Factory
{
    protected $model = Workplace::class;

    public function definition(): array
    {
        return [
            'location_name' => $this->faker->name(),
            'is_primary' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'professional_profile_id' => ProfessionalProfile::factory(),
            'address_id' => Address::factory(),
        ];
    }
}
