<?php

namespace Database\Seeders;

use App\Models\ProfessionalWorkplace;
use Illuminate\Database\Seeder;

class ProfessionalWorkplaceSeeder extends Seeder
{
    public function run(): void
    {
        ProfessionalWorkplace::factory(5)->create();
    }
}
