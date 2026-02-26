<?php

namespace Database\Factories;

use App\Models\Image;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ResourceFactory extends Factory
{
    protected $model = Resource::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'specialty' => $this->faker->word(),
            'type' => $this->faker->word(),
            'is_default' => $this->faker->boolean(),
            'is_active' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'pro_user_id' => User::factory(),
            'resource_image_id' => Image::factory(),
            'linked_user_id' => User::factory(),
        ];
    }
}
