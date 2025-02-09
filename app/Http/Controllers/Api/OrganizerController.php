<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizerResource;
use App\Models\Organizer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

final class OrganizerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $organizers = Organizer::with('user')->paginate();

        return OrganizerResource::collection($organizers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): OrganizerResource
    {
        $validated = $request->validate([
            'user_id' => 'required|ulid|exists:users,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:organizers,slug',
            'email' => 'nullable|email|unique:organizers,email',
            'phone' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'website' => 'nullable|url',
            'social_media' => 'nullable|url',
            'logo' => 'nullable|string',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $organizer = Organizer::create($validated);
        $organizer->load('user');

        return new OrganizerResource($organizer);
    }

    /**
     * Display the specified resource.
     */
    public function show(Organizer $organizer): OrganizerResource
    {
        $organizer->load('user');

        return new OrganizerResource($organizer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organizer $organizer): OrganizerResource
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|ulid|exists:users,id',
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255|unique:organizers,slug,' . $organizer->id,
            'email' => 'nullable|email|unique:organizers,email,' . $organizer->id,
            'phone' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'website' => 'nullable|url',
            'social_media' => 'nullable|url',
            'logo' => 'nullable|string',
        ]);

        if (isset($validated['name']) && empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $organizer->update($validated);
        $organizer->load('user');

        return new OrganizerResource($organizer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organizer $organizer): Response
    {
        $organizer->delete();

        return response()->noContent();
    }
}
