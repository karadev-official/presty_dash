<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'is_online' => ['sometimes', 'boolean'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'agenda_color' => ['sometimes', 'string', 'max:7'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
