<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Carte bleue',
                'slug' => 'card',
                'icon' => 'card-outline',
                'color' => '#3B82F6',
                'position' => 1,
            ],
            [
                'name' => 'Espèces',
                'slug' => 'cash',
                'icon' => 'cash-outline',
                'color' => '#10B981',
                'position' => 2,
            ],
            [
                'name' => 'Chèque',
                'slug' => 'check',
                'icon' => 'document-text-outline',
                'color' => '#F59E0B',
                'position' => 3,
            ],
            [
                'name' => 'Virement bancaire',
                'slug' => 'bank_transfer',
                'icon' => 'swap-horizontal-outline',
                'color' => '#8B5CF6',
                'position' => 4,
            ],
            [
                'name' => 'PayPal',
                'slug' => 'paypal',
                'icon' => 'logo-paypal',
                'color' => '#0070BA',
                'position' => 5,
            ],
            [
                'name' => 'Autre',
                'slug' => 'other',
                'icon' => 'ellipsis-horizontal-outline',
                'color' => '#6B7280',
                'position' => 99,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::create($method);
        }
    }
}
