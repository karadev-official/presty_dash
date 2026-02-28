<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'professional_profile_id',
        'customer_id',
        'resource_id',
        'workplace_id',
        'date',
        'start_time',
        'end_time',
        'duration',
        'status',
        'subtotal',
        'discount',
        'total',
        'customer_notes',
        'internal_notes',
        'payment_status',
        'deposit_amount',
        'deposit_paid_at',
        'deposit_payment_method',
        'amount_paid',
        'remaining_amount',
        'paid_at',
        'payment_method',
        'cancelled_at',
        'cancellation_reason',
        'cancelled_by',
        'reminder_sent',
        'reminder_sent_at',
        'confirmed_at',
        'completed_at',
    ];

    public function professionalProfile(): BelongsTo
    {
        return $this->belongsTo(ProfessionalProfile::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function workplace(): BelongsTo
    {
        return $this->belongsTo(Workplace::class);
    }

    public function services(): HasMany
    {
        return $this->HasMany(AppointmentService::class);
    }

    public function products(): HasMany
    {
        return $this->HasMany(AppointmentProduct::class);
    }

    protected function casts(): array
    {
        return [
            'duration' => 'integer',
            'subtotal' => 'integer',
            'discount' => 'integer',
            'total' => 'integer',
            'deposit_amount' => 'integer',
            'reminder_sent' => 'boolean',
        ];
    }

    // ========================================
    // ÉVÉNEMENTS (LOGIQUE DE STATS)
    // ========================================

    protected static function booted(): void
    {
        // ========================================
        // CRÉATION
        // ========================================
        static::created(function (Appointment $appointment) {
            // ✅ TOUS les RDV (même cancelled) sont comptés
            $appointment->customer->increment('total_appointments');

            // ✅ Si un acompte a été versé ET que le RDV n'est pas cancelled
            if ($appointment->status !== 'cancelled' && $appointment->deposit_amount > 0) {
                $appointment->customer->increment('total_spent', $appointment->deposit_amount);
            }
        });

        // ========================================
        // MISE À JOUR
        // ========================================
        static::updated(function (Appointment $appointment) {
            $customer = $appointment->customer;

            // Si le statut passe à "cancelled"
            if ($appointment->isDirty('status') && $appointment->status === 'cancelled') {
                $oldStatus = $appointment->getOriginal('status');

                if ($oldStatus !== 'cancelled') {
                    // ❌ On ne décrémente PAS total_appointments (même cancelled compte)

                    // ✅ Mais on soustrait les dépenses
                    if ($appointment->amount_paid > 0) {
                        $customer->decrement('total_spent', $appointment->amount_paid);
                    }
                }
            }

            // Si le statut passe de "cancelled" à un autre statut (réactivation)
            if ($appointment->isDirty('status') && $appointment->status !== 'cancelled') {
                $oldStatus = $appointment->getOriginal('status');

                if ($oldStatus === 'cancelled') {
                    // ✅ Ré-ajouter les dépenses
                    if ($appointment->amount_paid > 0) {
                        $customer->increment('total_spent', $appointment->amount_paid);
                    }
                }
            }

            // Si le montant payé change (et RDV non cancelled)
            if ($appointment->isDirty('amount_paid') && $appointment->status !== 'cancelled') {
                $oldAmountPaid = $appointment->getOriginal('amount_paid') ?? 0;
                $difference = $appointment->amount_paid - $oldAmountPaid;

                if ($difference > 0) {
                    $customer->increment('total_spent', $difference);
                } elseif ($difference < 0) {
                    $customer->decrement('total_spent', abs($difference));
                }
            }

            // Mettre à jour first_visit_at et last_visit_at
            if ($appointment->status === 'completed' && $appointment->isDirty('status')) {
                $visitDate = $appointment->completed_at ?? now();

                if (!$customer->first_visit_at) {
                    $customer->update(['first_visit_at' => $visitDate]);
                }

                $customer->update(['last_visit_at' => $visitDate]);
            }
        });

        // ========================================
        // SUPPRESSION
        // ========================================
        static::deleting(function (Appointment $appointment) {
            $customer = $appointment->customer;

            // ✅ Toujours décrémenter total_appointments (même si cancelled)
            $customer->decrement('total_appointments');

            // ✅ Mais soustraire les dépenses uniquement si non cancelled
            if ($appointment->status !== 'cancelled' && $appointment->amount_paid > 0) {
                $customer->decrement('total_spent', $appointment->amount_paid);
            }
        });
    }

    // ========================================
    // Accesseurs
    // ========================================

    /**
     * Date et heure de début combinées (timestamp Unix)
     */
    public function getDatetimeAttribute(): int
    {
        return Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->start_time)->timestamp;
    }

    /**
     * Date formatée pour l'affichage
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->date->locale('fr')->isoFormat('dddd D MMMM YYYY');
    }

    /**
     * Prix en euros
     */
    public function getPriceEurosAttribute(): float
    {
        return $this->price / 100;
    }

    /**
     * Montant payé en euros
     */
    public function getAmountPaidEurosAttribute(): float
    {
        return $this->amount_paid / 100;
    }

    /**
     * Vérifier si le RDV est passé
     */
    public function getIsPastAttribute(): bool
    {
        $datetime = Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->end_time);
        return $datetime->isPast();
    }

    /**
     * Vérifier si le RDV est aujourd'hui
     */
    public function getIsTodayAttribute(): bool
    {
        return $this->date->isToday();
    }

    /**
     * Vérifier si le RDV est demain
     */
    public function getIsTomorrowAttribute(): bool
    {
        return $this->date->isTomorrow();
    }

    // ========================================
    // Scopes
    // ========================================

    /**
     * Rendez-vous d'un professionnel
     */
    public function scopeForProfessional($query, int $professionalProfileId)
    {
        return $query->where('professional_profile_id', $professionalProfileId);
    }

    /**
     * Rendez-vous d'un client
     */
    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Rendez-vous par statut
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Rendez-vous en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Rendez-vous confirmés
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Rendez-vous terminés
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Rendez-vous annulés
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Rendez-vous à venir
     */
    public function scopeUpcoming($query)
    {
        return $query->whereDate('date', '>=', now())
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->orderBy('date')
            ->orderBy('start_time');
    }

    /**
     * Rendez-vous passés
     */
    public function scopePast($query)
    {
        return $query->whereDate('date', '<', now())
            ->orWhere(function ($q) {
                $q->whereDate('date', '=', now())
                    ->whereTime('end_time', '<', now()->format('H:i:s'));
            })
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc');
    }

    /**
     * Rendez-vous d'une date spécifique
     */
    public function scopeOnDate($query, string $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Rendez-vous entre deux dates
     */
    public function scopeBetweenDates($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Rendez-vous aujourd'hui
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', now());
    }

    /**
     * Rendez-vous demain
     */
    public function scopeTomorrow($query)
    {
        return $query->whereDate('date', now()->addDay());
    }

    /**
     * Tri par date et heure
     */
    public function scopeOrderByDatetime($query, string $direction = 'asc')
    {
        return $query->orderBy('date', $direction)
            ->orderBy('start_time', $direction);
    }

    // ========================================
    // Méthodes d'action
    // ========================================

    /**
     * Confirmer le rendez-vous
     */
    public function confirm(): self
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        // TODO: Envoyer notification au client

        return $this;
    }

    /**
     * Marquer comme terminé
     */
    public function complete(): self
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Mettre à jour les stats du customer
        $this->customer->recordVisit();
        if ($this->amount_paid > 0) {
            $this->customer->addSpent($this->amount_paid);
        }

        return $this;
    }

    /**
     * Annuler le rendez-vous
     */
    public function cancel(string $reason = null, int $cancelledBy = null): self
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
            'cancelled_by' => $cancelledBy,
        ]);

        return $this;
    }

    /**
     * Marquer comme payé
     */
    public function markAsPaid(string $method = null, int $amount = null): self
    {
        $this->update([
            'payment_status' => 'paid',
            'payment_method' => $method,
            'amount_paid' => $amount ?? $this->price,
            'paid_at' => now(),
        ]);

        return $this;
    }

    /**
     * Envoyer un rappel
     */
    public function sendReminder(): self
    {
        $this->update([
            'reminder_sent' => true,
            'reminder_sent_at' => now(),
        ]);

        // TODO: Envoyer notification/email

        return $this;
    }

    /**
     * Vérifier si un créneau est disponible
     */
    public static function isTimeSlotAvailable(
        int $professionalProfileId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeAppointmentId = null
    ): bool {
        $query = static::where('professional_profile_id', $professionalProfileId)
            ->whereDate('date', $date)
            ->whereNotIn('status', ['cancelled'])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q2) use ($startTime, $endTime) {
                        $q2->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            });

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        return !$query->exists();
    }


}
