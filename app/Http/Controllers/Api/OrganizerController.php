<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organizer\StoreOrganizerRequest;
use App\Http\Requests\Organizer\UpdateOrganizerRequest;
use App\Http\Resources\OrganizerResource;
use App\Models\Organizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class OrganizerController extends Controller
{
    /**
     * Display a listing of the organizers.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $organizers = Organizer::query()
            ->with(['user'])
            ->withCount([
                'events',
                'events as upcoming_events_count' => fn ($query) => $query->upcoming(),
                'events as past_events_count' => fn ($query) => $query->past(),
            ])
            ->when(
                $request->boolean('verified'),
                fn ($query) => $query->where('is_verified', true)
            )
            ->when(
                $request->has('include') && in_array('events', explode(',', $request->input('include'))),
                fn ($query) => $query->with(['events' => fn ($q) => $q->withCount('favorites')->latest()])
            )
            ->latest()
            ->paginate();

        return OrganizerResource::collection($organizers);
    }

    /**
     * Store a newly created organizer.
     */
    public function store(StoreOrganizerRequest $request): JsonResponse
    {
        $organizer = Organizer::create($request->validated());
        $organizer->loadCount('events')->load(['user']);

        return (new OrganizerResource($organizer))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified organizer.
     */
    public function show(Request $request, Organizer $organizer): OrganizerResource
    {
        $organizer->loadCount([
            'events',
            'events as upcoming_events_count' => fn ($query) => $query->upcoming(),
            'events as past_events_count' => fn ($query) => $query->past(),
        ])->load(['user']);

        if ($request->has('include') && in_array('events', explode(',', $request->input('include')))) {
            $events = $organizer->events()
                ->withCount('favorites')
                ->latest()
                ->paginate($request->input('per_page', 15));

            $organizer->setRelation('events', $events);
        }

        return new OrganizerResource($organizer);
    }

    /**
     * Update the specified organizer.
     */
    public function update(UpdateOrganizerRequest $request, Organizer $organizer): JsonResponse
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);

        $organizer->update($validated);
        $organizer->loadCount('events')->load(['user']);

        return (new OrganizerResource($organizer))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Remove the specified organizer.
     */
    public function destroy(Organizer $organizer): Response
    {

        $organizer->delete();

        return response()->noContent();
    }
}
