<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizerResource;
use App\Models\Organizer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

final class OrganizerController extends Controller
{
    /**
     * List organizers
     *
     * Display a listing of the organizers.
     */
    public function index(): AnonymousResourceCollection
    {
        $organizers = Organizer::query()
            ->when(
                request()->boolean('verified'),
                fn($query) => $query->where('is_verified', true),
            )
            ->paginate();

        return OrganizerResource::collection($organizers);
    }

    /**
     * Create organizer
     *
     * Create a new organizer.
     */
    public function store(Request $request): OrganizerResource
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

        $validated['user_id'] = auth()->id();
        $validated['slug'] = Str::slug($validated['name']);

        $organizer = Organizer::create($validated);

        return new OrganizerResource($organizer);
    }

    /**
     * Show organizer
     *
     * Display the specified organizer.
     */
    public function show(Organizer $organizer): OrganizerResource
    {
        return new OrganizerResource($organizer);
    }

    /**
     * Update organizer
     *
     * Update the specified organizer.
     */
    public function update(Request $request, Organizer $organizer): OrganizerResource
    {
        $this->authorize('update', $organizer);

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

        return new OrganizerResource($organizer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organizer $organizer): Response
    {
        $this->authorize('delete', $organizer);

        $organizer->delete();

        return response()->noContent();
    }
}
