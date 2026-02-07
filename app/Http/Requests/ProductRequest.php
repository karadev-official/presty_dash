<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest{
    public function rules()
    {
        return [
            'name' => ['required'],
'slug' => ['required'],
'description' => ['required'],
'user_id' => ['required', 'exists:users'],
'product_category_id' => ['required', 'exists:product_categories'],
'position' => ['required', 'integer'],
'price' => ['required', 'integer'],
'quantity' => ['nullable'],
'is_active' => ['boolean'],
'is_online' => ['boolean'],//
        ];
    }

    public function authorize()
    {
        return true;
    }
}
