<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceCategoryRequest extends FormRequest{
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users'],
'name' => ['required'],
'slug' => ['required'],
'is_active' => ['boolean'],
'is_online' => ['nullable', 'boolean'],
'position' => ['required', 'integer'],
'agenda_color' => ['nullable'],//
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
