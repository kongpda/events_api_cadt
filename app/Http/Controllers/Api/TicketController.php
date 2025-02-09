<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class TicketController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $tickets = Ticket::query()
            ->with(['event', 'user', 'ticketType'])
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->input('status'))
            )
            ->when(
                $request->filled('event_id'),
                fn ($query) => $query->where('event_id', $request->input('event_id'))
            )
            ->latest()
            ->paginate();

        return TicketResource::collection($tickets);
    }

    public function store(Request $request): TicketResource
    {
        $validated = $request->validate([
            'event_id' => ['required', 'string', Rule::exists('events', 'id')],
            'user_id' => ['required', 'string', Rule::exists('users', 'id')],
            'ticket_type_id' => ['required', 'string', Rule::exists('ticket_types', 'id')],
            'status' => ['required', 'string', Rule::in(['pending', 'confirmed', 'cancelled'])],
            'purchase_date' => ['required', 'date'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        $ticket = DB::transaction(fn () => Ticket::create($validated));

        return new TicketResource($ticket->load(['event', 'user', 'ticketType']));
    }

    public function show(Ticket $ticket): TicketResource
    {
        return new TicketResource($ticket->load(['event', 'user', 'ticketType']));
    }

    public function update(Request $request, Ticket $ticket): TicketResource
    {
        $validated = $request->validate([
            'event_id' => ['sometimes', 'string', Rule::exists('events', 'id')],
            'user_id' => ['sometimes', 'string', Rule::exists('users', 'id')],
            'ticket_type_id' => ['sometimes', 'string', Rule::exists('ticket_types', 'id')],
            'status' => ['sometimes', 'string', Rule::in(['pending', 'confirmed', 'cancelled'])],
            'purchase_date' => ['sometimes', 'date'],
            'price' => ['sometimes', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        $ticket = DB::transaction(function () use ($ticket, $validated) {
            $ticket->update($validated);

            return $ticket;
        });

        return new TicketResource($ticket->load(['event', 'user', 'ticketType']));
    }

    public function destroy(Ticket $ticket): JsonResponse
    {
        DB::transaction(function () use ($ticket): void {
            $ticket->delete();
        });

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
