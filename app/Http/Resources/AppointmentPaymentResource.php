<?php

namespace App\Http\Resources;

use App\Models\AppointmentPayment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AppointmentPayment */
class AppointmentPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'is_deposit' => $this->is_deposit,
            'notes' => $this->notes,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
            'payment_method' => new PaymentMethodResource($this->paymentMethod),
        ];
    }
}
