<?php

namespace App\Http\Controllers\Api\v1\Pro;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCategoryRequest;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        return ProductCategoryResource::collection(ProductCategory::where("user_id", $request->user()->id)->get());
    }

    public function store(ProductCategoryRequest $request)
    {
        $data = $request->validated();
        return new ProductCategoryResource(ProductCategory::create(
            [
                'user_id' => $request->user()->id,
                ...$data
            ])
        );
    }

    public function show(Request $request, ProductCategory $category)
    {
        abort_unless($category->user_id === $request->user()->id, 404);
        return new ProductCategoryResource($category);
    }

    public function update(ProductCategoryRequest $request, ProductCategory $category)
    {
        abort_unless($category->user_id === $request->user()->id, 404);
        $category->update($request->validated());
        return new ProductCategoryResource($category);
    }

    public function destroy(Request $request, ProductCategory $category)
    {
        abort_unless($category->user_id === $request->user()->id, 404);
        $category->delete();
        return response()->json([], 204);
    }
}
