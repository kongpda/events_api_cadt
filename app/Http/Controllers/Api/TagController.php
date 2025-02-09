<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class TagController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $tags = Tag::with('events')
            ->when(
                request()->boolean('active'),
                fn ($query) => $query->where('is_active', true)
            )
            ->paginate();

        return TagResource::collection($tags);
    }

    public function store(Request $request): TagResource
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'position' => ['integer', 'min:0'],
        ]);

        $tag = Tag::create([
            ...$validated,
            'slug' => Str::slug($validated['name']),
        ]);

        return new TagResource($tag);
    }

    public function show(Tag $tag): TagResource
    {
        return new TagResource($tag->load('events'));
    }

    public function update(Request $request, Tag $tag): TagResource
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'position' => ['integer', 'min:0'],
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $tag->update($validated);

        return new TagResource($tag);
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
