<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function index(Request $request)
    {

        $categories = ServiceCategory::with('services')->where("user_id", $request->user()->id)->get();
        $categories = $categories->map(function ($category) {
            return $this->ServiceCategoryPayload($category);
        });


        return response()->json(
            [
                'categories' => $categories
            ]
        );
    }

    public function ServiceCategoryPayload(ServiceCategory $category)
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'is_active' => $category->is_active,
            'is_online' => $category->is_online,
            'position' => $category->position,
            'agenda_color' => $category->agenda_color,
            'services' => $category->services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'slug' => $service->slug,
                    'description' => $service->description,
                    'duration' => $service->duration,
                    'price' => $service->price,
                    'is_active' => $service->is_active,
                    'is_online' => $service->is_online,
                    'option_groups' => $service->optionGroups->map(function ($optionGroup) {
                        return [
                            'id' => $optionGroup->id,
                            'name' => $optionGroup->name,
                            'selection_type' => $optionGroup->selection_type,
                            'is_required' => $optionGroup->is_required,
                            'min_select' => $optionGroup->min_select,
                            'max_select' => $optionGroup->max_select,
                            'position' => $optionGroup->pivot->position,
                            'options' => $optionGroup->options->map(function ($option) {
                                return [
                                    'id' => $option->id,
                                    'name' => $option->name,
                                    'price' => $option->price,
                                    'position' => $option->position,
                                ];
                            }),
                        ];
                    }),
                ];
            }),

        ];
    }
}
