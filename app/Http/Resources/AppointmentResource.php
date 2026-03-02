<?php

namespace App\Http\Resources;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Appointment */
class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'duration' => $this->duration,
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'total' => $this->total,
            'total_eur' => $this->totalEuros."€",
            'customer_notes' => $this->customer_notes,
            'internal_notes' => $this->internal_notes,
            'cancelled_at' => $this->cancelled_at,
            'cancellation_reason' => $this->cancellation_reason,
            'reminder_sent' => $this->reminder_sent,
            'reminder_sent_at' => $this->reminder_sent_at,
            'confirmed_at' => $this->confirmed_at,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,

            'professional_profile_id' => $this->professional_profile_id,
            'customer_id' => $this->customer_id,
            'resource_id' => $this->resource_id,
            'cancelled_by' => $this->cancelled_by,

            'services' => $this->services,
            'products' => $this->products,

            'customer' => [
                'id' => $this->customer->id,
                'display_name' => $this->customer->display_name,
                'custom_phone' => $this->customer->custom_phone,
                'custom_email' => $this->customer->custom_email,
                'initials' => $this->customer->initials,
            ],
            'workplace' => new WorkplaceResource($this->workplace),
            'cancelledBy' => new UserResource($this->cancelledBy),
            'payments' => AppointmentPaymentResource::collection($this->payments),
        ];
    }
}
