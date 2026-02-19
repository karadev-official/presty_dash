<?php

namespace Database\Seeders;

use App\Models\Workplace;
use Illuminate\Database\Seeder;

class WorkplaceSeeder extends Seeder
{
    public function run(): void
    {
        Workplace::factory(5)->create();
    }
}
