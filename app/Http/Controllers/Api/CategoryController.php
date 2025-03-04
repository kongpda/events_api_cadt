<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class CategoryController extends Controller
{
    /**
     * All categories
     *
     * Display a listing of the categories.
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::query()
            ->orderBy('position')
            ->orderBy('name')
            ->paginate();

        return CategoryResource::collection($categories);
    }

    /**
     * Create New Category
     *
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return response()->json([
            'message' => 'Category created successfully',
            'data' => new CategoryResource($category),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show Category by slug
     *
     * Display the specified category.
     */
    public function show(Category $category): CategoryResource
    {
        $category->load(['events' => function ($query): void {
            $query->withCount('favorites')
                ->with('organizer');
        }]);

        return CategoryResource::make($category);
    }

    /**
     * Update Category by slug
     *
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => new CategoryResource($category),
        ], Response::HTTP_OK);
    }

    /**
     * Remove Category by slug
     *
     * Remove the specified category from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ], Response::HTTP_OK);
    }
}
