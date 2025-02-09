<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class EventController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return EventResource::collection(
            Event::with(['categories', 'tags'])->latest()->paginate(10)
        );
    }

    public function store(Request $request): EventResource
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:events,slug',
            'description' => 'required|string|max:65535',
            'address' => 'required|string|max:255',
            'feature_image' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|string|in:draft,published,archived',
            'user_id' => 'required|ulid|exists:users,id',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'required|array',
            'tags.*' => 'exists:tags,id',
        ]);

        $event = Event::query()->create($validatedData);

        // Sync relationships
        if ($request->has('categories')) {
            $event->categories()->sync($request->input('categories'));
        }

        if ($request->has('tags')) {
            $event->tags()->sync($request->input('tags'));
        }

        return new EventResource(
            $event->load(['categories', 'tags'])
        );
    }

    public function show(Event $event): EventResource
    {
        return new EventResource(
            $event->load(['categories', 'tags'])
        );
    }

    public function update(Request $request, Event $event): EventResource
    {
        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:events,slug,' . $event->id,
            'description' => 'sometimes|required|string|max:65535',
            'address' => 'sometimes|required|string|max:255',
            'feature_image' => 'nullable|string|max:255',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'sometimes|required|string|in:draft,published,archived',
            'user_id' => 'sometimes|required|ulid|exists:users,id',
            'categories' => 'sometimes|required|array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'sometimes|required|array',
            'tags.*' => 'exists:tags,id',
        ]);

        $event->update($validatedData);

        // Sync relationships
        if ($request->has('categories')) {
            $event->categories()->sync($request->input('categories'));
        }

        if ($request->has('tags')) {
            $event->tags()->sync($request->input('tags'));
        }

        return new EventResource(
            $event->load(['categories', 'tags'])
        );
    }

    public function destroy(Event $event): Response
    {
        $event->delete();

        return response()->noContent();
    }
}
