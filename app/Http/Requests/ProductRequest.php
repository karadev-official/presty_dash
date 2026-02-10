<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function rules(): array
    {
        if(request()->isMethod("POST")) {
            return [
                'name' => ['required'],
                'slug' => ['required'],
                'description' => ['required'],
                'product_category_id' => ['required', 'exists:product_categories,id'],
                'position' => ['sometimes','required', 'integer'],
                'price' => ['required', 'integer'],
                'quantity' => ['nullable'],
                'is_active' => ['boolean'],
                'is_online' => ['boolean'],
                'image_ids' => ['sometimes', 'array'],
            ];
        }
        return [
            'name' => ['sometimes','required'],
            'slug' => ['sometimes', 'required'],
            'description' => ['sometimes', 'required'],
            'position' => ['sometimes', 'required', 'integer'],
            'price' => ['sometimes', 'required', 'integer'],
            'quantity' => ['sometimes', 'nullable'],
            'is_active' => ['sometimes', 'boolean'],
            'is_online' => ['sometimes', 'boolean'],
            'image_ids' => ['sometimes', 'array'],
        ];

    }

    public function authorize(): bool
    {
        return true;
    }
}
