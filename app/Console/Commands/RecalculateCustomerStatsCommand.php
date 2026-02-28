<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateCustomerStatsCommand extends Command
{
    protected $signature = 'customers:recalculate-stats {--customer-id= : ID d\'un customer spécifique}';
    protected $description = 'Recalculer les stats de tous les customers';

    public function handle(): int
    {
        $customerId = $this->option('customer-id');

        if ($customerId) {
            $customers = Customer::where('id', $customerId)->get();
            if ($customers->isEmpty()) {
                $this->error("Customer #{$customerId} introuvable !");
                return Command::FAILURE;
            }
        } else {
            $customers = Customer::all();
        }

        $this->info("🔄 Recalcul des stats pour {$customers->count()} customer(s)...");
        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();

        foreach ($customers as $customer) {
            $this->recalculateForCustomer($customer);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Stats recalculées avec succès !');

        return Command::SUCCESS;
    }

    private function recalculateForCustomer(Customer $customer): void
    {
        // ✅ TOUS les RDV (même cancelled) pour total_appointments
        $allAppointments = $customer->appointments()->get();
        $totalAppointments = $allAppointments->count();

        // ✅ Uniquement les RDV NON cancelled pour total_spent
        $nonCancelledAppointments = $allAppointments->where('status', '!=', 'cancelled');
        $totalSpent = $nonCancelledAppointments->sum('amount_paid');

        // ✅ Première et dernière visite (RDV completed uniquement)
        $completedAppointments = $allAppointments->where('status', 'completed');

        $firstVisit = null;
        $lastVisit = null;

        if ($completedAppointments->isNotEmpty()) {
            $firstCompleted = $completedAppointments->sortBy(function ($apt) {
                return $apt->completed_at ?? Carbon::parse($apt->date . ' ' . $apt->end_time);
            })->first();

            $lastCompleted = $completedAppointments->sortByDesc(function ($apt) {
                return $apt->completed_at ?? Carbon::parse($apt->date . ' ' . $apt->end_time);
            })->first();

            $firstVisit = $firstCompleted->completed_at
                ?? Carbon::parse($firstCompleted->date . ' ' . $firstCompleted->end_time);

            $lastVisit = $lastCompleted->completed_at
                ?? Carbon::parse($lastCompleted->date . ' ' . $lastCompleted->end_time);
        }

        // Mettre à jour
        $customer->update([
            'total_appointments' => $totalAppointments,
            'total_spent' => $totalSpent,
            'first_visit_at' => $firstVisit,
            'last_visit_at' => $lastVisit,
        ]);
    }
}
