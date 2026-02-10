<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductCategoryRequest extends FormRequest
{
    public function rules() : array
    {
        if(request()->isMethod("POST")) {
            return [
                'name' => ['required'],
                'slug' => ['required', "nullable", "string"],
                'position' => ['nullable', 'integer'],
                'is_active' => ['sometimes', 'boolean'],
                'is_online' => ['sometimes', 'boolean'],
            ];
        }
        return [
            'name' => ['sometimes', 'required', 'string'],
            'slug' => ['sometimes', 'required', 'string'],
            'position' => ['sometimes', 'required', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
            'is_online' => ['sometimes', 'boolean'],
        ];
    }

    public function authorize() : bool
    {
        return true;
    }
}
