<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function rules(): array
    {
        if(request()->isMethod("POST")) {
            return [
                'professional_profile_id' => ['required', 'exists:professional_profiles,id'],
                'user_id' => ['nullable', 'exists:users,id'],
                'display_name' => ['sometimes', 'nullable', 'string'],
                'custom_email' => ['nullable', 'email', 'max:254'],

                'notes' => ['nullable'],
                'custom_phone' => ['nullable'],
                'avatar_image_id' => ['nullable', 'exists:images,id'],
                'tags' => ['nullable'],
                'preferences' => ['nullable'],
                'is_favorite' => ['sometimes','boolean'],
                'is_blocked' => ['sometimes','boolean'],
                'first_visit_at' => ['nullable', 'date'],
                'last_visit_at' => ['nullable', 'date'],
                'total_appointments' => ['sometimes', 'integer'],
                'total_spent' => ['sometimes', 'integer'],
            ];
        }
        return [
            'user_id' => ['sometimes', 'exists:users,id'],
            'display_name' => ['sometimes', 'nullable', 'string'],
            'custom_email' => ['sometimes','nullable', 'email', 'max:254'],
            'notes' => ['sometimes', 'nullable'],
            'custom_phone' => ['sometimes','nullable'],
            'avatar_image_id' => ['sometimes','nullable', 'exists:images,id'],
            'tags' => ['sometimes','nullable'],
            'preferences' => ['sometimes','nullable'],
            'is_favorite' => ['sometimes','sometimes','boolean'],
            'is_blocked' => ['sometimes','boolean'],
            'first_visit_at' => ['sometimes','nullable', 'date'],
            'last_visit_at' => ['sometimes','nullable', 'date'],
            'total_appointments' => ['sometimes', 'integer'],
            'total_spent' => ['sometimes', 'integer'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
