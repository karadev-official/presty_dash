<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'professional_profile_id',
        'customer_id',
        'resource_id',
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

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => 'array',
            'payment_status' => 'array',
            'deposit_paid_at' => 'timestamp',
            'paid_at' => 'timestamp',
            'cancelled_at' => 'timestamp',
            'reminder_sent' => 'boolean',
            'reminder_sent_at' => 'timestamp',
            'confirmed_at' => 'timestamp',
            'completed_at' => 'timestamp',
        ];
    }
}
