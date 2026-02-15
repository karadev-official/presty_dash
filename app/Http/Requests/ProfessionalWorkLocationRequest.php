<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfessionalWorkLocationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'professional_profile_id' => ['required', 'exists:professional_profiles'],
            'address_id' => ['required', 'exists:addresses'],
            'location_name' => ['required'],
            'is_primary' => ['boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
