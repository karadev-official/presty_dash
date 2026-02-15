<?php

namespace Database\Seeders;

use App\Models\ProfessionalWorkLocation;
use Illuminate\Database\Seeder;

class ProfessionalWorkLocationSeeder extends Seeder
{
    public function run(): void
    {
        ProfessionalWorkLocation::factory(5)->create();
    }
}
