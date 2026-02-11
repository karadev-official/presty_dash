<?php

namespace Database\Factories;

use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ProfessionalProfileFactory extends Factory
{
    protected $model = ProfessionalProfile::class;

    public function definition(): array
    {
        return [
            'specialty' => $this->faker->word(),
            'company_name' => $this->faker->name(),
            'siret' => $this->faker->word(),
            'description' => $this->faker->text(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'pro_user_id' => User::factory(),
        ];
    }
}
