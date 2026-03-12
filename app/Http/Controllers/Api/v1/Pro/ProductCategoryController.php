<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCategoryRequest;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use App\Models\ProfessionalProfile;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        $professionalProfile = $request->user()->professionalProfile;
        return ProductCategoryResource::collection(ProductCategory::where("professional_profile_id", $professionalProfile->id)->get());
    }

    public function store(ProductCategoryRequest $request)
    {
        $this->authorize('create', ProductCategory::class);
        $data = $request->validated();
        return new ProductCategoryResource(ProductCategory::create(
            [
                'professional_profile_id' => $request->user()->professionalProfile->id,
                ...$data
            ])
        );
    }

    public function show(Request $request, ProductCategory $category)
    {
        $this->authorize('view', $category);
        return new ProductCategoryResource($category);
    }

    public function update(ProductCategoryRequest $request, ProductCategory $category)
    {
        $this->authorize('update', $category);
        $category->update($request->validated());
        return new ProductCategoryResource($category);
    }

    public function destroy(Request $request, ProductCategory $category)
    {
        $this->authorize('delete', $category);
        $category->delete();
        return response()->json([], 204);
    }
}
