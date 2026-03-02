<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentPaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'appointment_id' => ['required', 'exists:appointments'],
            'payment_method_id' => ['required', 'exists:payment_methods'],
            'amount' => ['required', 'integer'],
            'is_deposit' => ['boolean'],
            'notes' => ['nullable'],
            'paid_at' => ['required', 'date'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
