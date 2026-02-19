<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'professional_profile_id' => ['required', 'exists:professional_profiles,id'],
            'user_id' => ['required', 'exists:users,id'],
            'avatar_image_id' => ['required', 'exists:images,id'],
            'display_name' => ['nullable'],
            'notes' => ['nullable'],
            'custom_phone' => ['nullable'],
            'custom_email' => ['nullable', 'email', 'max:254'],
            'tags' => ['nullable'],
            'preferences' => ['nullable'],
            'is_favorite' => ['boolean'],
            'is_blocked' => ['boolean'],
            'first_visit_at' => ['nullable', 'date'],
            'last_visit_at' => ['nullable', 'date'],
            'total_appointments' => ['required', 'integer'],
            'total_spent' => ['required', 'integer'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
