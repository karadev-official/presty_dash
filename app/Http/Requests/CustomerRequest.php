<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'professional_profile_id' => ['required', 'exists:professional_profiles,id'],
            'user_id' => ['sometimes', 'nullable', 'exists:users,id'],
            'display_name' => [$this->requiredIfPost(), 'nullable', 'string'],
            'custom_email' => ['nullable', 'email', 'max:254'],

            'notes' => ['nullable'],
            'custom_phone' => ['nullable'],
            'avatar_image_id' => ['nullable', 'exists:images,id'],
            'tags' => ['sometimes','nullable'],
            'preferences' => ['sometimes','nullable'],
            'is_favorite' => ['sometimes','boolean'],
            'is_blocked' => ['sometimes','boolean'],
            'first_visit_at' => ['sometimes','nullable', 'date'],
            'last_visit_at' => ['sometimes','nullable', 'date'],
            'total_appointments' => ['sometimes', 'integer'],
            'total_spent' => ['sometimes', 'integer'],
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
