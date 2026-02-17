<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfessionalWorkplaceRequest extends FormRequest
{
    public function rules(): array
    {
        if(request()->isMethod("POST")) {
            return [
//                'professional_profile_id' => ['required', 'exists:professional_profiles,id'],
                'address_id' => ['required', 'exists:addresses,id'],
                'location_name' => ['required'],
                'is_primary' => ['boolean'],
            ];
        }
        return [
            'professional_profile_id' => ['sometimes','required', 'exists:professional_profiles,id'],
            'address_id' => ['sometimes', 'exists:addresses,id'],
            'location_name' => ['sometimes', 'required', 'string'],
            'is_primary' => ['sometimes','boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
