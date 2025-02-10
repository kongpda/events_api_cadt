<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizerResource;
use App\Models\Organizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

final class OrganizerController extends Controller
{
    /**
     * Display a listing of the organizers.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $organizers = Organizer::query()
            ->when(
                $request->boolean('verified'),
                fn ($query) => $query->where('is_verified', true),
            )
            ->paginate();

        return OrganizerResource::collection($organizers);
    }

    /**
     * Store a newly created organizer.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:organizers'],
            'phone' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'website' => ['nullable', 'url'],
            'social_media' => ['nullable', 'url'],
            'logo' => ['nullable', 'string'],
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['slug'] = Str::slug($validated['name']);

        $organizer = Organizer::create($validated);

        return response()->json([
            'message' => 'Organizer created successfully',
            'data' => new OrganizerResource($organizer),
        ], 201);
    }

    /**
     * Display the specified organizer.
     */
    public function show(Organizer $organizer): OrganizerResource
    {
        return new OrganizerResource($organizer);
    }

    /**
     * Update the specified organizer.
     */
    public function update(Request $request, Organizer $organizer): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', Rule::unique('organizers')->ignore($organizer->id)],
            'phone' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'website' => ['nullable', 'url'],
            'social_media' => ['nullable', 'url'],
            'logo' => ['nullable', 'string'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $organizer->update($validated);

        return response()->json([
            'message' => 'Organizer updated successfully',
            'data' => new OrganizerResource($organizer),
        ]);
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
