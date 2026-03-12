<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateCustomerStatsCommand extends Command
{
    protected $signature = 'customers:recalculate-stats {--customer-id= : ID d\'un customer spécifique}';
    protected $description = 'Recalculer les stats de tous les customers (avec paiements)';

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
        // ✅ Compter TOUS les RDV (même cancelled)
        $totalAppointments = DB::table('appointments')
            ->where('customer_id', $customer->id)
            ->whereNull('deleted_at')
            ->count();

        // ✅ Calculer total_spent depuis les paiements (RDV NON cancelled uniquement)
        $totalSpent = DB::table('appointment_payments')
            ->join('appointments', 'appointment_payments.appointment_id', '=', 'appointments.id')
            ->where('appointments.customer_id', $customer->id)
            ->where('appointments.status', '!=', 'cancelled')
            ->whereNull('appointments.deleted_at')
            ->whereNull('appointment_payments.deleted_at')
            ->sum('appointment_payments.amount');

        // ✅ Première visite (RDV completed)
        $firstVisit = DB::table('appointments')
            ->where('customer_id', $customer->id)
            ->where('status', 'completed')
            ->whereNull('deleted_at')
            ->orderBy(DB::raw('COALESCE(completed_at, CONCAT(date, " ", end_time))'))
            ->value(DB::raw('COALESCE(completed_at, CONCAT(date, " ", end_time))'));

        // ✅ Dernière visite (RDV completed)
        $lastVisit = DB::table('appointments')
            ->where('customer_id', $customer->id)
            ->where('status', 'completed')
            ->whereNull('deleted_at')
            ->orderByDesc(DB::raw('COALESCE(completed_at, CONCAT(date, " ", end_time))'))
            ->value(DB::raw('COALESCE(completed_at, CONCAT(date, " ", end_time))'));

        // Mettre à jour
        $customer->update([
            'total_appointments' => $totalAppointments,
            'total_spent' => (int) $totalSpent,
            'first_visit_at' => $firstVisit ? Carbon::parse($firstVisit) : null,
            'last_visit_at' => $lastVisit ? Carbon::parse($lastVisit) : null,
        ]);
    }
}
