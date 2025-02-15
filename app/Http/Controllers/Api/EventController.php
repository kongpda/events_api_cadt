<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\UploadImage;
use App\Http\Controllers\Controller;
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
        private readonly ImageService $imageService
    ) {}

    /**
     * Display a listing of the events.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $events = Event::query()
            ->with(['category', 'user', 'organizer', 'tags', 'favorites'])
            ->withCount('favorites')
            ->when($request->filled('category_id'), function ($query) use ($request): void {
                $query->where('category_id', $request->input('category_id'));
            })
            ->when($request->filled('organizer_id'), function ($query) use ($request): void {
                $query->where('organizer_id', $request->input('organizer_id'));
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
     * Display the specified event.
     */
    public function show(Event $event): EventResource
    {
        $event->load(['category', 'user', 'organizer', 'tags'])
            ->loadCount('favorites');

        if (auth()->check()) {
            $event->loadExists(['favorites as is_favorited' => function ($query): void {
                $query->where('user_id', auth()->id());
            }]);
        }

        return new EventResource($event);
    }

    /**
     * Update the specified event.
     */
    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
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
