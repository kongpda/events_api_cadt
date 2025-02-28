<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\AuthorizeOrganizerAction;
use App\Actions\UploadImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Event\SearchEventRequest;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class EventController extends Controller
{
    public function __construct(
        private readonly UploadImage $uploadImage,
        private readonly ImageService $imageService,
        private readonly AuthorizeOrganizerAction $authorizeOrganizer
    ) {}

    /**
     * All Event Lists
     *
     * Display a listing of the events.
     */
    public function index(SearchEventRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $events = Event::query()
            ->with(['category', 'user.profile', 'organizer', 'tags', 'favorites'])
            ->withCount('favorites')
            ->when($validated['search'] ?? null, function ($query) use ($validated): void {
                $query->where('title', 'like', "%{$validated['search']}%");
            })
            ->when($validated['location'] ?? null, function ($query) use ($validated): void {
                $query->where('location', 'like', "%{$validated['location']}%");
            })
            ->when($validated['category_id'] ?? null, function ($query) use ($validated): void {
                $query->where('category_id', $validated['category_id']);
            })
            ->when($validated['organizer_id'] ?? null, function ($query) use ($validated): void {
                $query->where('organizer_id', $validated['organizer_id']);
            })
            ->when($request->user(), function ($query) use ($request): void {
                $query->withExists(['favorites as is_favorited' => function ($query) use ($request): void {
                    $query->where('user_id', $request->user()->id);
                }]);
            })
            ->latest()
            ->paginate();

        return EventResource::collection($events);
    }

    /**
     * Create a new event
     *
     * Store a newly created event.
     */
    public function store(StoreEventRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $tags = collect($validated['tags'] ?? []);
        unset($validated['tags']);

        if ($request->hasFile('feature_image')) {
            $validated['feature_image'] = $this->imageService->upload(
                file: $request->file('feature_image'),
                path: 'events/features'
            );
        }

        $event = Event::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        if ( ! empty($tags)) {
            $event->tags()->sync($tags);
        }

        return response()->json([
            'message' => 'Event created successfully',
            'data' => new EventResource($event->load(['category', 'tags'])),
        ], 201);
    }

    /**
     * Show event details
     *
     * Display the specified event.
     */
    public function show(Event $event): EventResource
    {
        $event->load(['category', 'user.profile', 'organizer', 'tags'])
            ->loadCount('favorites');

        if (auth()->check()) {
            $event->loadExists(['favorites as is_favorited' => function ($query): void {
                $query->where('user_id', auth()->id());
            }]);
        }

        return new EventResource($event);
    }

    /**
     * Update event details
     *
     * Update the specified event.
     */
    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        if ( ! $this->authorizeOrganizer->execute($request->user(), $event)) {
            return response()->json([
                'message' => 'You are not authorized to update this event.',
            ], 403);
        }

        $validated = $request->validated();
        $tags = collect($validated['tags'] ?? []);
        unset($validated['tags']);

        if ($request->hasFile('feature_image')) {
            $validated['feature_image'] = $this->imageService->upload(
                file: $request->file('feature_image'),
                path: 'events/features',
                oldImage: $event->feature_image
            );
        }

        $event->update($validated);

        if ($request->has('tags')) {
            $event->tags()->sync($tags);
        }

        // Load the event with necessary relationships and counts
        $event->load(['category', 'tags'])
            ->loadCount('favorites');

        // Load is_favorited if user is authenticated
        if (auth()->check()) {
            $event->loadExists(['favorites as is_favorited' => function ($query): void {
                $query->where('user_id', auth()->id());
            }]);
        }

        return response()->json([
            'message' => 'Event updated successfully',
            'data' => new EventResource($event),
        ]);
    }

    /**
     * Delete event
     *
     * Remove the specified event by id.
     */
    public function destroy(Event $event): Response
    {
        $event->delete();

        return response()->noContent();
    }

    /**
     * User Events
     *
     * Display a listing of the user's events.
     */
    public function userEvents(Request $request): AnonymousResourceCollection
    {
        $events = Event::query()
            ->with(['category', 'user.profile', 'organizer', 'tags', 'favorites'])
            ->withCount('favorites')
            ->where('user_id', $request->user()->id)
            ->when($request->filled('category_id'), function ($query) use ($request): void {
                $query->where('category_id', $request->input('category_id'));
            })
            ->when($request->user(), function ($query) use ($request): void {
                $query->withExists(['favorites as is_favorited' => function ($query) use ($request): void {
                    $query->where('user_id', $request->user()->id);
                }]);
            })
            ->latest()
            ->paginate();

        return EventResource::collection($events);
    }

    /**
     * Organizer Events
     *
     * Display a listing of the organizer's events.
     */
    public function organizerEvents(Request $request): AnonymousResourceCollection
    {
        $organizerId = $request->user()->organizer?->id;

        $events = Event::query()
            ->with(['category', 'user.profile', 'organizer', 'tags', 'favorites'])
            ->withCount('favorites')
            ->where('organizer_id', $organizerId)
            ->when($request->filled('category_id'), function ($query) use ($request): void {
                $query->where('category_id', $request->input('category_id'));
            })
            ->when($request->user(), function ($query) use ($request): void {
                $query->withExists(['favorites as is_favorited' => function ($query) use ($request): void {
                    $query->where('user_id', $request->user()->id);
                }]);
            })
            ->latest()
            ->paginate();

        return EventResource::collection($events);
    }

    /**
     * Feature Events
     *
     * Display a listing of featured events.
     */
    public function featured(Request $request): AnonymousResourceCollection
    {
        $events = Event::query()
            ->with(['category', 'user.profile', 'organizer', 'tags', 'featuredEvent'])
            ->withCount('favorites')
            ->whereHas('featuredEvent', function ($query): void {
                $query->active();
            })
            ->when($request->user(), function ($query) use ($request): void {
                $query->withExists(['favorites as is_favorited' => function ($query) use ($request): void {
                    $query->where('user_id', $request->user()->id);
                }]);
            })
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Return empty collection if no featured events found
        if ($events->isEmpty()) {
            return EventResource::collection(collect());
        }

        return EventResource::collection($events);
    }
}
