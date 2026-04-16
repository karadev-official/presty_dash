<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Availability;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class SlotGeneratorService
{
    /**
     * Génère les créneaux disponibles pour un professionnel sur une période
     *
     * @param int $professionalProfileId
     * @param int $durationMin Durée du service en minutes
     * @param string|null $startDate Date de début (Y-m-d)
     * @param int $daysAhead Nombre de jours à calculer
     * @return array
     */

    public function getAvailableSlots(
        int $professionalProfileId,
        int $durationMin,
        ?string $startDate = null,
        int $daysAhead = 30,
    ): array
    {
        $availability = Availability::with([
            'weekDays.ranges',
            'weekDays.blockedSlots',
            'dateOverrides.ranges',
            'dateOverrides.blockedSlots',
        ])->where('professional_profile_id', $professionalProfileId)->first();

        if (!$availability) {
            return [];
        }

        $start = $startDate ? Carbon::parse($startDate) : Carbon::today();
        $end = $start->copy()->addDays($daysAhead);
        $timezone = $availability->timezone ?? 'Europe/Paris';

        $period = CarbonPeriod::create($start, $end);
        $result = [];

        foreach ($period as $date) {
            $slots = $this->getSlotsForDate(
                $availability,
                $date,
                $durationMin,
                $timezone,
                $professionalProfileId
            );

            if (!empty($slots)) {
                $result[] = [
                    'date' => $date->format('Y-m-d'),
                    'day_name' => $this->getDayNameFr($date->dayOfWeek),
                    'full_date' => $this->getFullDateFr($date),
                    'slots' => $slots,
                ];
            }
        }
        return $result;
    }

    /**
     * Récupère les créneaux pour une date donnée
     */
    protected function getSlotsForDate(
        Availability $availability,
        Carbon $date,
        int $durationMin,
        string $timezone,
        string $professionalProfileId
    ): array
    {
        // Vérifier si override pour cette date
        $override = $availability->dateOverrides()
            ->where('date', $date->format('Y-m-d'))
            ->first();

        // Si jour off
        if ($override && $override->is_off) {
            return [];
        }

        // Si override avec ranges customs
        if ($override && $override->ranges->isNotEmpty()) {
            return $this->generateSlotsFromRanges(
                $override->ranges,
                $override->blockedSlots ?? collect(),
                $date,
                $durationMin,
                $timezone,
                $professionalProfileId
            );
        }

        // Sinon, utiliser la config hebdomadaire
        $weekday = $date->dayOfWeek; // 0 = dimanche, 6 = samedi
        $weekDayConfig = $availability->weekDays()
            ->where('weekday', $weekday)
            ->where('enabled', true)
            ->first();

        if (!$weekDayConfig || $weekDayConfig->ranges->isEmpty()) {
            return [];
        }

        return $this->generateSlotsFromRanges(
            $weekDayConfig->ranges,
            $weekDayConfig->blockedSlots ?? collect(),
            $date,
            $durationMin,
            $timezone,
            $professionalProfileId
        );
    }

    /**
     * Génère les créneaux à partir de ranges horaires
     */
    protected function generateSlotsFromRanges(
        Collection $ranges,
        Collection $blockedSlots,
        Carbon $date,
        int $durationMin,
        string $timezone,
        int $professionalProfileId
    ): array
    {
        $slots = [];
        foreach ($ranges as $range) {
            $start = Carbon::parse($date->format('Y-m-d') . ' ' . $range->start_time, $timezone);
            $end = Carbon::parse($date->format('Y-m-d') . ' ' . $range->end_time, $timezone);

            // Générer les créneaux toutes les X minutes
            $current = $start->copy();

            while ($current->copy()->addMinutes($durationMin)->lte($end)) {
                $slotStart = $current->copy();
                $slotEnd = $slotStart->copy()->addMinutes($durationMin);

                // Vérifier si le créneau n'est pas bloqué
                if (!$this->isSlotBlocked($slotStart, $slotEnd, $blockedSlots)) {
                    // Vérifier si pas déjà pris (RDV existants)
                    if (!$this->isSlotBooked($slotStart, $slotEnd, $professionalProfileId)) {
                        $slots[] = [
                            'id' => uniqid('slot_'),
                            'date' => $date->format('Y-m-d'),
                            'time' => $slotStart->format('H:i'),
                            'end_time' => $slotEnd->format('H:i'),
                        ];
                    }
                }

                $current->addMinutes($durationMin);
            }
        }
        return $slots;
    }

    /**
     * Vérifie si un créneau est bloqué
     */
    protected function isSlotBlocked(Carbon $start, Carbon $end, Collection $blockedSlots): bool
    {
        foreach ($blockedSlots as $blockedSlot) {
            $blockedStart = Carbon::parse($start->format('Y-m-d') . ' ' . $blockedSlot->start_time);
            $blockedEnd = Carbon::parse($start->format('Y-m-d') . ' ' . $blockedSlot->end_time);

            // Vérifie s'il y a chevauchement
            if ($start->lt($blockedEnd) && $end->gt($blockedStart)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Vérifie si un créneau est déjà réservé
     */
    protected function isSlotBooked(Carbon $start, Carbon $end, int $professionalProfileId): bool
    {
        // Vérifier dans la table appointments
        return Appointment::where('professional_profile_id', $professionalProfileId)
            ->where('date', $start->format('Y-m-d'))
            ->where('status', '!=', 'cancelled') // Exclure les annulés
            ->where(function ($q) use ($start, $end) {
                // Vérifie s'il y a chevauchement avec un RDV existant
                $q->where(function ($query) use ($start, $end) {
                    // Le début du RDV est dans notre créneau
                    $query->where('start_time', '>=', $start->format('H:i:s'))
                        ->where('start_time', '<', $end->format('H:i:s'));
                })
                    ->orWhere(function ($query) use ($start, $end) {
                        // La fin du RDV est dans notre créneau
                        $query->where('end_time', '>', $start->format('H:i:s'))
                            ->where('end_time', '<=', $end->format('H:i:s'));
                    })
                    ->orWhere(function ($query) use ($start, $end) {
                        // Notre créneau est complètement dans un RDV
                        $query->where('start_time', '<=', $start->format('H:i:s'))
                            ->where('end_time', '>=', $end->format('H:i:s'));
                    });
            })
            ->exists();
    }


    /**
     * Retourne le nom du jour en français
     */
    protected function getDayNameFr(int $dayOfWeek): string
    {
        $days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        return $days[$dayOfWeek];
    }

    /**
     * Retourne la date complète en français
     */
    protected function getFullDateFr(Carbon $date): string
    {
        $months = [
            1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
            'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
        ];

        return sprintf(
            '%s %d %s %d',
            $this->getDayNameFr($date->dayOfWeek),
            $date->day,
            $months[$date->month],
            $date->year
        );
    }
}
