<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductCategoryRequest extends FormRequest{
    public function rules()
    {
        return [
            'user_id' => ['required', 'exists:users'],
'name' => ['required'],
'slug' => ['required'],
'position' => ['required', 'integer'],//
        ];
    }

    public function authorize()
    {
        return true;
    }
}
