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
    /**
     * List events
     *
     * Display a listing of the events.
     */
    public function index(): AnonymousResourceCollection
    {
        $events = Event::with(['category', 'user', 'organizer', 'tags'])
            ->latest()
            ->paginate();

        return EventResource::collection($events);
    }

    /**
     * Create event.
     *
     * Create a new event.
     */
    public function store(Request $request): EventResource
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:events,slug',
            'description' => 'required|string|max:65535',
            'address' => 'required|string|max:255',
            'feature_image' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'category_id' => 'required|exists:categories,id',
            'user_id' => 'required|ulid|exists:users,id',
            'organizer_id' => 'required|ulid|exists:organizers,id',
            'participation_type' => 'required|string|in:paid,free',
            'capacity' => 'required|integer|min:1',
            'registration_deadline' => 'required|date|before:start_date',
            'registration_status' => 'required|string|in:open,closed,full',
            'event_type' => 'required|string|in:in_person,online,hybrid',
            'online_url' => 'required_if:event_type,online,hybrid|nullable|url',
            'tags' => 'required|array',
            'tags.*' => 'exists:tags,id',
        ]);

        // Generate slug if not provided
        if ( ! isset($validatedData['slug'])) {
            $validatedData['slug'] = str()->slug($validatedData['title']);
        }

        $event = Event::query()->create($validatedData);

        if ($request->has('tags')) {
            $event->tags()->sync($request->input('tags'));
        }

        return new EventResource(
            $event->load(['category', 'tags']),
        );
    }

    /**
     * Show event.
     *
     * Display the specified event.
     */
    public function show(Event $event): EventResource
    {
        return new EventResource($event->load(['category', 'user', 'organizer', 'tags']));
    }

    public function update(Request $request, Event $event): EventResource
    {
        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:events,slug,' . $event->id,
            'description' => 'sometimes|required|string|max:65535',
            'address' => 'sometimes|required|string|max:255',
            'feature_image' => 'nullable|string|max:255',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'category_id' => 'sometimes|required|exists:categories,id',
            'user_id' => 'sometimes|required|ulid|exists:users,id',
            'organizer_id' => 'sometimes|required|ulid|exists:organizers,id',
            'participation_type' => 'sometimes|required|string|in:paid,free',
            'capacity' => 'sometimes|required|integer|min:1',
            'registration_deadline' => 'sometimes|required|date|before:start_date',
            'registration_status' => 'sometimes|required|string|in:open,closed,full',
            'event_type' => 'sometimes|required|string|in:in_person,online,hybrid',
            'online_url' => 'required_if:event_type,online,hybrid|nullable|url',
            'tags' => 'sometimes|required|array',
            'tags.*' => 'exists:tags,id',
        ]);

        // Generate slug if title is updated but slug is not provided
        if (isset($validatedData['title']) && ! isset($validatedData['slug'])) {
            $validatedData['slug'] = str()->slug($validatedData['title']);
        }

        $event->update($validatedData);

        if ($request->has('tags')) {
            $event->tags()->sync($request->input('tags'));
        }

        return new EventResource(
            $event->load(['category', 'tags']),
        );
    }

    /**
     * Delete event.
     *
     * Delete the specified event.
     */
    public function destroy(Event $event): Response
    {
        $event->delete();

        return response()->noContent();
    }
}
