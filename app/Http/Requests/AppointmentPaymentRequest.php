<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentPaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'appointment_id' => [$this->requiredIfPost(), 'exists:appointments,id'],
            'payment_method_id' => [$this->requiredIfPost(), 'exists:payment_methods,id'],
            'amount' => [$this->requiredIfPost(), 'integer', 'min:0'],
            'is_deposit' => ['sometimes','boolean'],
            'notes' => ['sometimes','nullable'],
            'paid_at' => [$this->requiredIfPost(), 'date'],
        ];
    }

    protected function requiredIfPost(): string
    {
        return $this->isMethod('POST') ? 'required' : 'sometimes';
    }

    public function authorize(): bool
    {
        return true;
    }
}
