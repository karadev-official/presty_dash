<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentServiceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'appointment_id' => ['required', 'exists:appointments'],
            'service_id' => ['required', 'exists:services'],
            'price' => ['required', 'integer'],
            'duration' => ['required', 'integer'],
            'quantity' => ['required', 'integer'],
            'total' => ['required', 'integer'],
            'notes' => ['nullable'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
