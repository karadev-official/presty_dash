<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyCustomerStatsCommand extends Command
{

    protected $signature = 'customers:verify-stats {customer-id?}';
    protected $description = 'Vérifier les stats d\'un customer';

    public function handle(): int
    {
        $customerId = $this->argument('customer-id');

        if (!$customerId) {
            $this->error('Veuillez fournir un customer-id');
            return Command::FAILURE;
        }

        $customer = Customer::find($customerId);

        if (!$customer) {
            $this->error("Customer #{$customerId} introuvable !");
            return Command::FAILURE;
        }

        $this->info("📊 Vérification pour : {$customer->display_name}");
        $this->newLine();

        // Calculer les vraies valeurs
        $allAppointments = $customer->appointments()->get();
        $actualTotalAppointments = $allAppointments->count();

        $nonCancelledAppointments = $allAppointments->where('status', '!=', 'cancelled');
        $actualTotalSpent = $nonCancelledAppointments->sum('amount_paid');

        // Afficher
        $this->table(
            ['Champ', 'Valeur enregistrée', 'Valeur calculée', 'OK ?'],
            [
                [
                    'total_appointments',
                    $customer->total_appointments,
                    $actualTotalAppointments,
                    $customer->total_appointments === $actualTotalAppointments ? '✅' : '❌'
                ],
                [
                    'total_spent',
                    $customer->total_spent,
                    $actualTotalSpent,
                    $customer->total_spent === $actualTotalSpent ? '✅' : '❌'
                ],
            ]
        );

        if ($customer->total_appointments !== $actualTotalAppointments ||
            $customer->total_spent !== $actualTotalSpent) {
            $this->error('❌ Incohérence détectée !');
            $this->info('💡 Exécutez : php artisan customers:recalculate-stats --customer-id=' . $customerId);
            return Command::FAILURE;
        }

        $this->info('✅ Tout est cohérent !');
        return Command::SUCCESS;
    }
}
