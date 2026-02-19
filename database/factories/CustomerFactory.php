<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Image;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'display_name' => $this->faker->name(),
            'notes' => $this->faker->word(),
            'custom_phone' => $this->faker->phoneNumber(),
            'custom_email' => $this->faker->unique()->safeEmail(),
            'tags' => $this->faker->words(),
            'preferences' => $this->faker->words(),
            'is_favorite' => $this->faker->boolean(),
            'is_blocked' => $this->faker->boolean(),
            'first_visit_at' => Carbon::now(),
            'last_visit_at' => Carbon::now(),
            'total_appointments' => $this->faker->randomNumber(),
            'total_spent' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'professional_profile_id' => 1 ?? ProfessionalProfile::factory(),
            'user_id' => User::factory(),
            'avatar_image_id' => Image::first()->id,
        ];
    }
}
