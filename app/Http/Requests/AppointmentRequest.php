<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'professional_profile_id' => ['required', 'exists:professional_profiles'],
            'customer_id' => ['required', 'exists:customers'],
            'resource_id' => ['nullable', 'exists:resources'],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date'],
            'duration' => ['required', 'integer'],
            'status' => ['required'],
            'subtotal' => ['required', 'integer'],
            'discount' => ['required', 'integer'],
            'total' => ['required', 'integer'],
            'customer_notes' => ['nullable'],
            'internal_notes' => ['nullable'],
            'payment_status' => ['required'],
            'deposit_amount' => ['required', 'integer'],
            'deposit_paid_at' => ['nullable', 'date'],
            'deposit_payment_method' => ['nullable'],
            'amount_paid' => ['required', 'integer'],
            'remaining_amount' => ['required', 'integer'],
            'paid_at' => ['nullable', 'date'],
            'payment_method' => ['required'],
            'cancelled_at' => ['nullable', 'date'],
            'cancellation_reason' => ['nullable'],
            'cancelled_by' => ['nullable', 'exists:users'],
            'reminder_sent' => ['boolean'],
            'reminder_sent_at' => ['nullable', 'date'],
            'confirmed_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
