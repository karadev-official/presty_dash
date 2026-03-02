<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class AppointmentPayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'appointment_id',
        'payment_method_id',
        'amount',
        'is_deposit',
        'notes',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'is_deposit' => 'boolean',
            'paid_at' => 'datetime',
        ];
    }

    // ========================================
    // RELATIONS
    // ========================================

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    // ========================================
    // ACCESSEURS
    // ========================================

    public function getAmountEurosAttribute(): float
    {
        return $this->amount / 100;
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopeDeposits($query)
    {
        return $query->where('is_deposit', true);
    }

    public function scopeNonDeposits($query)
    {
        return $query->where('is_deposit', false);
    }

    // ========================================
    // ÉVÉNEMENTS (STATS CUSTOMER)
    // ========================================

    protected static function booted(): void
    {
        // Lorsque qu'un paiement est créé
        static::created(function (AppointmentPayment $payment) {

            $appointment = $payment->appointment;

            Log::info('AppointmentPayment Creation', [
                'appointment_id' => $appointment->id,
                'status' => $appointment->status,
                'customer_id' => $appointment->customer_id,
            ]);

            if ($appointment->status !== 'cancelled') {
                $appointment->customer->increment('total_spent', $payment->amount);
            }
        });

        // Quand un paiement est mis à jour
        static::updated(function (AppointmentPayment $payment) {
            $appointment = $payment->appointment;

            // Si le montant a changé
            if ($payment->isDirty('amount') && $appointment->status !== 'cancelled'){
                $oldAmount = $payment->getOriginal('amount') ?? 0;
                $difference = $payment->amount - $oldAmount;

                if ($difference > 0) {
                    $appointment->customer->increment('total_spent', $difference);
                } elseif ($difference < 0) {
                    $appointment->customer->decrement('total_spent', abs($difference));
                }
            }
        });

        // Quand un paiement est supprimé
        static::deleting(function (AppointmentPayment $payment) {
            // À ce stade, le paiement est supprimé mais on a encore accès aux données
            $appointment = Appointment::find($payment->appointment_id);

            if ($appointment && $appointment->status !== 'cancelled' && $payment->amount > 0) {
                $customer = $appointment->customer;
                if ($customer) {
                    $customer->decrement('total_spent', $payment->amount);
                }
            }
        });
    }
}
