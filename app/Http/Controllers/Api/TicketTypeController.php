<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketTypeRequest;
use App\Http\Requests\UpdateTicketTypeRequest;
use App\Http\Resources\TicketTypeResource;
use App\Models\TicketType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class TicketTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return TicketTypeResource::collection(
            TicketType::with(['event', 'user'])->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketTypeRequest $request): TicketTypeResource
    {
        $ticketType = TicketType::create($request->validated());

        return TicketTypeResource::make($ticketType->load(['event', 'user']));
    }

    /**
     * Display the specified resource.
     */
    public function show(TicketType $ticketType): TicketTypeResource
    {
        return TicketTypeResource::make($ticketType->load(['event', 'user']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketTypeRequest $request, TicketType $ticketType): TicketTypeResource
    {
        $ticketType->update($request->validated());

        return TicketTypeResource::make($ticketType->load(['event', 'user']));
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
