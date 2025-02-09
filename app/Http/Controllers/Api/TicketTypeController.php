<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketTypeResource;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class TicketTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $ticketTypes = TicketType::with(['event', 'user'])->paginate();

        return TicketTypeResource::collection($ticketTypes);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): void {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): TicketTypeResource
    {
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'user_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:draft,published,sold_out'],
        ]);

        $ticketType = TicketType::create($validated);

        return new TicketTypeResource($ticketType->load(['event', 'user']));
    }

    /**
     * Display the specified resource.
     */
    public function show(TicketType $ticketType): TicketTypeResource
    {
        return new TicketTypeResource($ticketType->load(['event', 'user']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TicketType $ticketType): void {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TicketType $ticketType): TicketTypeResource
    {
        $validated = $request->validate([
            'event_id' => ['sometimes', 'exists:events,id'],
            'user_id' => ['sometimes', 'exists:users,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:draft,published,sold_out'],
        ]);

        $ticketType->update($validated);

        return new TicketTypeResource($ticketType->load(['event', 'user']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TicketType $ticketType): Response
    {
        $ticketType->delete();

        return response()->noContent();
    }
}
