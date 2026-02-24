<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'appointment_id' => ['required', 'exists:appointments'],
            'product_id' => ['required', 'exists:products'],
            'price' => ['required', 'integer'],
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
