<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [$this->requiredIfPost(), 'string'],
            'slug' => [$this->requiredIfPost(), 'string'],
            'description' => ['sometimes', 'nullable', 'string'],
            'product_category_id' => [$this->requiredIfPost(), 'exists:product_categories,id'],
            'position' => ['sometimes', 'integer'],
            'price' => [$this->requiredIfPost(), 'numeric', 'min:0'],
            'quantity' => [$this->requiredIfPost(), 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'is_online' => ['sometimes', 'boolean'],
            'image_ids' => ['nullable', 'array'],
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
