<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfessionalProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'pro_user_id' => ['required', 'exists:users'],
            'specialty' => ['nullable'],
            'company_name' => ['nullable'],
            'siret' => ['nullable'],
            'description' => ['nullable'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
