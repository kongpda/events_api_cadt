<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;

final class EventController extends Controller
{
    public function index()
    {
        return EventResource::collection(Event::all());
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'slug' => 'required|unique:events,slug',
            'venue_id' => 'required|exists:venues,id',
            'category_id' => 'required|exists:categories,id',
            'feature_image' => 'nullable|url',
            'content' => 'required|array',
            'event_date' => 'required|array',
            'action_content' => 'nullable|array',
            'user_id' => 'required|exists:users,id',
            // Note: tag_id is not included as it's a many-to-many relationship
        ]);

        $event = Event::create($validatedData);

        // Handle tags separately if provided
        if ($request->has('tags')) {
            $event->tags()->sync($request->input('tags'));
        }

        return new EventResource($event);
    }

    public function show(Event $event)
    {
        return new EventResource($event);
    }

    public function update(Request $request, Event $event)
    {
        $validatedData = $request->validate([
            'title' => 'sometimes|required|max:255',
            'slug' => 'sometimes|required|unique:events,slug,' . $event->id,
            'venue_id' => 'sometimes|required|exists:venues,id',
            'category_id' => 'sometimes|required|exists:categories,id',
            'feature_image' => 'nullable|url',
            'content' => 'sometimes|required|array',
            'event_date' => 'sometimes|required|array',
            'action_content' => 'nullable|array',
            'user_id' => 'sometimes|required|exists:users,id',
        ]);

        $event->update($validatedData);

        // Handle tags separately if provided
        if ($request->has('tags')) {
            $event->tags()->sync($request->input('tags'));
        }

        return new EventResource($event);
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json(null, 204);
    }
}
