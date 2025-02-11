<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class EventController extends Controller
{
    /**
     * Display a listing of the events.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $events = Event::query()
            ->with(['category', 'user', 'organizer', 'tags', 'favoritedBy'])
            ->when($request->filled('category_id'), function ($query) use ($request): void {
                $query->where('category_id', $request->input('category_id'));
            })
            ->when($request->filled('organizer_id'), function ($query) use ($request): void {
                $query->where('organizer_id', $request->input('organizer_id'));
            })
            ->when($request->user(), function ($query) use ($request): void {
                $query->withExists(['favoritedBy as is_favorited' => function ($query) use ($request): void {
                    $query->where('user_id', $request->user()->id);
                }]);
            })
            ->latest()
            ->paginate();

        return EventResource::collection($events);
    }

    /**
     * Store a newly created event.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:events,slug'],
            'description' => ['required', 'string', 'max:65535'],
            'address' => ['required', 'string', 'max:255'],
            'feature_image' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'category_id' => ['required', 'exists:categories,id'],
            'organizer_id' => ['required', 'ulid', 'exists:organizers,id'],
            'participation_type' => ['required', 'string', 'in:paid,free'],
            'capacity' => ['required', 'integer', 'min:1'],
            'registration_deadline' => ['required', 'date', 'before:start_date'],
            'registration_status' => ['required', 'string', 'in:open,closed,full'],
            'event_type' => ['required', 'string', 'in:in_person,online,hybrid'],
            'online_url' => ['required_if:event_type,online,hybrid', 'nullable', 'url'],
            'tags' => ['required', 'array'],
            'tags.*' => ['exists:tags,id'],
        ]);

        // Generate slug if not provided
        if ( ! isset($validated['slug'])) {
            $validated['slug'] = str()->slug($validated['title']);
        }

        // Set the authenticated user as the creator
        $validated['user_id'] = $request->user()->id;

        $event = Event::create($validated);

        if ($request->has('tags')) {
            $event->tags()->sync($request->input('tags'));
        }

        return response()->json([
            'message' => 'Event created successfully',
            'data' => new EventResource($event->load(['category', 'tags'])),
        ], 201);
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event): EventResource
    {
        $event->load(['category', 'user', 'organizer', 'tags']);

        if (auth()->check()) {
            $event->loadExists(['favoritedBy as is_favorited' => function ($query): void {
                $query->where('user_id', auth()->id());
            }]);
        }

        return new EventResource($event);
    }

    /**
     * Update the specified event.
     */
    public function update(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'unique:events,slug,' . $event->id],
            'description' => ['sometimes', 'required', 'string', 'max:65535'],
            'address' => ['sometimes', 'required', 'string', 'max:255'],
            'feature_image' => ['nullable', 'string', 'max:255'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date', 'after:start_date'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'organizer_id' => ['sometimes', 'required', 'ulid', 'exists:organizers,id'],
            'participation_type' => ['sometimes', 'required', 'string', 'in:paid,free'],
            'capacity' => ['sometimes', 'required', 'integer', 'min:1'],
            'registration_deadline' => ['sometimes', 'required', 'date', 'before:start_date'],
            'registration_status' => ['sometimes', 'required', 'string', 'in:open,closed,full'],
            'event_type' => ['sometimes', 'required', 'string', 'in:in_person,online,hybrid'],
            'online_url' => ['required_if:event_type,online,hybrid', 'nullable', 'url'],
            'tags' => ['sometimes', 'required', 'array'],
            'tags.*' => ['exists:tags,id'],
        ]);

        // Generate slug if title is updated but slug is not provided
        if (isset($validated['title']) && ! isset($validated['slug'])) {
            $validated['slug'] = str()->slug($validated['title']);
        }

        $event->update($validated);

        if ($request->has('tags')) {
            $event->tags()->sync($request->input('tags'));
        }

        return response()->json([
            'message' => 'Event updated successfully',
            'data' => new EventResource($event->load(['category', 'tags'])),
        ]);
    }

    /**
     * Remove the specified event.
     *
     * @BearerAuth
     */
    public function destroy(Event $event): Response
    {
        $event->delete();

        return response()->noContent();
    }
}
