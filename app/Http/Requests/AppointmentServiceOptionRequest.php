<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentServiceOptionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'appointment_service_id' => ['required', 'exists:appointment_services'],
            'service_option_id' => ['required', 'exists:service_options'],
            'service_option_group_id' => ['required', 'exists:service_option_groups'],
            'option_name' => ['required'],
            'group_name' => ['required'],
            'price' => ['required', 'integer'],
            'duration' => ['required', 'integer'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
