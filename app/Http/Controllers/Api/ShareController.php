<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShareResource;
use App\Models\Share;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

final class ShareController extends Controller
{
    /**
     * List shares
     *
     * Display a listing of the shares.
     */
    public function index(): AnonymousResourceCollection
    {
        $shares = Share::with(['user', 'event'])->paginate();

        return ShareResource::collection($shares);
    }

    /**
     * Create share
     *
     * Create a new share.
     */
    public function store(Request $request): ShareResource
    {
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'platform' => ['required', 'string'],
            'share_url' => ['required', 'url'],
        ]);

        $share = Share::create([
            'user_id' => $request->user()->id,
            ...$validated,
        ]);

        return new ShareResource($share->load(['user', 'event']));
    }

    /**
     * Show share
     *
     * Display the specified share.
     */
    public function show(Share $share): ShareResource
    {
        return new ShareResource($share->load(['user', 'event']));
    }

    /**
     * Delete share
     *
     * Delete the specified share.
     */
    public function destroy(Share $share): JsonResponse
    {
        $share->delete();

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
