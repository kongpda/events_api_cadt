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
            ->when(
                $request->boolean('verified'),
                fn ($query) => $query->where('is_verified', true)
            )
            ->when(
                $request->has('include') && in_array('events', explode(',', $request->input('include'))),
                fn ($query) => $query->with(['events' => fn ($q) => $q->latest()])
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
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        $validated['slug'] = Str::slug($validated['name']);

        $organizer = Organizer::create($validated);
        $organizer->load(['user']);

        return (new OrganizerResource($organizer))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified organizer.
     */
    public function show(Request $request, Organizer $organizer): OrganizerResource
    {
        $organizer->load(['user']);

        if ($request->has('include') && in_array('events', explode(',', $request->input('include')))) {
            $organizer->load(['events' => fn ($query) => $query->latest()]);
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
        $organizer->load(['user']);

        return (new OrganizerResource($organizer))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Remove the specified organizer.
     */
    public function destroy(Organizer $organizer): Response
    {
        $this->authorize('delete', $organizer);

        $organizer->delete();

        return response()->noContent();
    }
}
