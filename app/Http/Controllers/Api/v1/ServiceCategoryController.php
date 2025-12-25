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

    public function show(Request $request, ServiceCategory $category)
    {
        abort_unless($category->user_id === $request->user()->id, 404);

        $category->load('services.optionGroups.options');

        return response()->json(
            [
                'category' => $this->ServiceCategoryPayload($category)
            ]
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'is_online' => ['sometimes', 'boolean'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'agenda_color' => ['sometimes', 'string', 'max:7'],
        ]);

        // vérifier unicité du slug par pro
        $exists = ServiceCategory::where('user_id', $request->user()->id)
            ->where('slug', $data['slug'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'La catégorie existe déjà.'], 422);
        }

        $category = ServiceCategory::create([
            'user_id' => $request->user()->id,
            ...$data,
        ]);

        return response()->json(
            [
                'category' => $this->ServiceCategoryPayload($category)
            ],
            201
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
