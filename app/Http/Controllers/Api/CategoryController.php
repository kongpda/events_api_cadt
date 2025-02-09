<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class CategoryController extends Controller
{
    /**
     * List categories
     *
     * Display a listing of the categories.
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::query()
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }

    /**
     * Create category.
     *
     * Create a new category.
     */
    public function store(Request $request): CategoryResource
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:120|unique:categories',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'position' => 'integer',
        ]);

        $category = Category::query()->create($validatedData);

        return new CategoryResource($category);
    }

    /**
     * Show category.
     *
     * Display the specified category.
     */
    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }

    public function update(Request $request, Category $category): CategoryResource
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'slug' => 'sometimes|required|string|max:120|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'position' => 'integer',
        ]);

        $category->update($validatedData);

        return new CategoryResource($category);
    }

    /**
     * Delete category.
     *
     * Delete the specified category.
     */
    public function destroy(Category $category): Response
    {
        $category->delete();

        return response()->noContent();
    }
}
