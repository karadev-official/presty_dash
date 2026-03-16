<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            PaymentMethodSeeder::class
        ]);
        $user = User::firstOrCreate(
            ['email' => 'karamoko@presty.app'],
            [
                'name' => 'Karamoko',
                'password' => bcrypt('Karamoko'),
                'email_verified_at' => now(),
            ]
        );
        $user->assignRole('pro', 'super-admin');
    }
}
