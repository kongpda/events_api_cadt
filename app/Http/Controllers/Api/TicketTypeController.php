<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TicketType\StoreTicketTypeRequest;
use App\Http\Requests\TicketType\UpdateTicketTypeRequest;
use App\Http\Resources\TicketTypeResource;
use App\Models\TicketType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class TicketTypeController extends Controller
{
    /**
     * List ticket types
     *
     * Display a listing of the ticket types.
     */
    public function index(): AnonymousResourceCollection
    {
        return TicketTypeResource::collection(
            TicketType::with(['event', 'creator'])->paginate(),
        );
    }

    /**
     * Create ticket type.
     *
     * Create a new ticket type.
     */
    public function store(StoreTicketTypeRequest $request): TicketTypeResource
    {
        $ticketType = TicketType::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return TicketTypeResource::make($ticketType->load(['event', 'creator']));
    }

    /**
     * Show ticket type.
     *
     * Display the specified ticket type.
     */
    public function show(TicketType $ticketType): TicketTypeResource
    {
        return TicketTypeResource::make($ticketType->load(['event', 'creator']));
    }

    /**
     * Update ticket type.
     *
     * Update the specified ticket type.
     */
    public function update(UpdateTicketTypeRequest $request, TicketType $ticketType): TicketTypeResource
    {
        $ticketType->update($request->validated());

        return TicketTypeResource::make($ticketType->load(['event', 'creator']));
    }

    /**
     * Delete ticket type.
     *
     * Delete the specified ticket type.
     */
    public function destroy(TicketType $ticketType): Response
    {
        $ticketType->delete();

        return response()->noContent();
    }
}
