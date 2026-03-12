<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\LoyaltyCard;
use App\Models\LoyaltyProgram;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    /**
     * Vérifier si une récompense est disponible pour ce RDV
     * Appelé AVANT de créer le RDV (pour afficher un message au client)
     */
    public function checkRewardForAppointment(Customer $customer, int $professionalProfileId, int $appointmentTotal): ?array
    {
        $program = LoyaltyProgram::where('professional_profile_id', $professionalProfileId)
            ->where('is_active', true)
            ->first();

        if (!$program) {
            return null;
        }

        // Vérifier le montant minimum
        if ($appointmentTotal < $program->min_appointment_amount) {
            return null;
        }

        // Créer ou récupérer la carte
        $card = LoyaltyCard::firstOrCreate(
            [
                'customer_id' => $customer->id,
                'loyalty_program_id' => $program->id,
            ],
            [
                'total_visits' => 0,
                'is_active' => true,
            ]
        );

        // La prochaine visite sera : total_visits + 1
        $nextVisitNumber = $card->total_visits + 1;

        // Chercher si un palier correspond exactement à ce numéro de visite
        $reward = $program->rewards()
            ->where('is_active', true)
            ->where('required_visits', $nextVisitNumber)
            ->first();

        if (!$reward) {
            return null;
        }

        return [
            'discount_amount' => $reward->discount_amount,
            'visit_number' => $nextVisitNumber,
            'message' => "Félicitations ! C'est votre {$nextVisitNumber}ème visite, vous bénéficiez de " . ($reward->discount_amount / 100) . "€ de réduction !",
        ];
    }

    /**
     * Enregistrer la visite ET appliquer la récompense si disponible
     * Appelé lors de la création du RDV
     */
    public function processVisit(Appointment $appointment): ?array
    {
        $professionalProfile = $appointment->professionalProfile;
        $customer = $appointment->customer;

        $program = $professionalProfile->loyaltyProgram()
            ->where('is_active', true)
            ->first();

        if (!$program) {
            return null;
        }

        // Vérifier le montant minimum
        if ($appointment->total < $program->min_appointment_amount) {
            return null;
        }

        // Créer ou récupérer la carte
        $card = LoyaltyCard::firstOrCreate(
            [
                'customer_id' => $customer->id,
                'loyalty_program_id' => $program->id,
            ],
            [
                'total_visits' => 0,
                'is_active' => true,
            ]
        );

        return DB::transaction(function () use ($card, $program) {
            // Incrémenter le compteur
            $card->increment('total_visits');
            $card->update(['last_visit_at' => now()]);

            $currentVisit = $card->total_visits;

            // Vérifier si un palier correspond à cette visite
            $reward = $program->rewards()
                ->where('is_active', true)
                ->where('required_visits', $currentVisit)
                ->first();

            if ($reward) {
                return [
                    'discount_amount' => $reward->discount_amount,
                    'visit_number' => $currentVisit,
                    'message' => "Félicitations ! C'est votre {$currentVisit}ème visite, vous bénéficiez de " . ($reward->discount_amount / 100) . "€ de réduction !",
                ];
            }

            return null;
        });
    }

    /**
     * Stats d'une carte
     */
    public function getCardStats(LoyaltyCard $card): array
    {
        // Prochaine récompense disponible
        $nextReward = $card->loyaltyProgram->rewards()
            ->where('is_active', true)
            ->where('required_visits', '>', $card->total_visits)
            ->orderBy('required_visits')
            ->first();

        $progressPercentage = 0;
        $visitsRemaining = 0;

        if ($nextReward) {
            $progressPercentage = min(100, (int)(($card->total_visits / $nextReward->required_visits) * 100));
            $visitsRemaining = max(0, $nextReward->required_visits - $card->total_visits);
        }

        return [
            'total_visits' => $card->total_visits,
            'next_reward' => $nextReward,
            'progress_percentage' => $progressPercentage,
            'visits_remaining' => $visitsRemaining,
            'last_visit_at' => $card->last_visit_at,
        ];
    }
}
