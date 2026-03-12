<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [$this->requiredIfPost(), 'string', 'max:255'],
            'slug' => [$this->requiredIfPost(), 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'is_online' => ['sometimes', 'boolean'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'agenda_color' => ['sometimes', 'string', 'max:7'],
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
