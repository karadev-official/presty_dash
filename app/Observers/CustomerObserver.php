<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\LoyaltyCard;

class CustomerObserver
{
    public function created(Customer $customer): void
    {
        $loyaltyProgram = $customer->professionalProfile->loyaltyProgram;

        if ($loyaltyProgram?->is_active) {
            LoyaltyCard::firstOrCreate([
                'customer_id' => $customer->id,
                'loyalty_program_id' => $loyaltyProgram->id,
            ], [
                'total_visits' => 0,
                'is_active' => true,
            ]);
        }
    }

    /**
     * ✅ Désactiver la carte lors d'un soft delete
     */
    public function deleted(Customer $customer): void
    {
        if ($customer->isForceDeleting()) {
            // Si force delete, supprimer la carte définitivement
            $customer->loyaltyCard?->forceDelete();
        } else {
            // Si soft delete, juste désactiver
            $customer->loyaltyCard?->update(['is_active' => false]);
        }
    }

    /**
     * ✅ Réactiver la carte lors d'une restauration
     */
    public function restored(Customer $customer): void
    {
        $customer->loyaltyCard?->update(['is_active' => true]);
    }
}
