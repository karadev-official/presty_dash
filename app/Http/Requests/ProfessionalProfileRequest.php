<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfessionalProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'pro_user_id' => ['required', 'exists:users,id'],
            'specialty' => ['sometimes','nullable'],
            'company_name' => ['sometimes','nullable'],
            'siret' => ['sometimes','nullable'],
            'description' => ['sometimes','nullable'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
