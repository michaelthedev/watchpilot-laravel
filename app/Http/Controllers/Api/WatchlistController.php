<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Media;
use App\Models\Watchlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class WatchlistController extends BaseController
{
    public function index(): JsonResponse
    {
        $watchlists = $this->getUser()->watchlists()
            ->withCount('media')
            ->orderBy('name')
            ->get();

        return $this->jsonResponse(
            message: 'User watchlists',
            data: $watchlists
        );
    }

    public function show(string $uid): JsonResponse
    {
        $watchlist = Watchlist::where('uid', $uid)
            ->with('items')
            ->first();

        if (!$watchlist) {
            return $this->jsonResponse(
                message: 'Watchlist not found',
                status: 404
            );
        }

        $user = $this->getUser();

        // Check if watchlist belongs to user or is public
        if ($watchlist->user_id !== $user?->id && !$watchlist->isPublic()) {
            return $this->jsonResponse(
                message: 'Unauthorized',
                status: 403
            );
        }

        if ($watchlist->isPublic() && $watchlist->user_id !== $user?->id) {
            $watchlist->incrementViews();
        }

        return $this->jsonResponse(
            message: 'Watchlist details',
            data: $watchlist->with('media')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'visibility' => ['sometimes', 'in:public,private']
        ]);

        $user = $this->getUser();
        $visibility = $request->visibility ?? 'private';

        $watchlist = $user->watchlists()->create([
            'name' => $request->name,
            'visibility' => $visibility,
            'uid' => Str::uuid(),
        ]);

        return $this->jsonResponse(
            message: 'Watchlist created',
            data: $watchlist->only(['uid']),
            status: 201
        );
    }

    public function update(Request $request, string $uid): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'visibility' => ['sometimes', 'in:public,private']
        ]);

        $watchlist = $this->getUser()->watchlists()
            ->where('uid', $uid)->first();

        if (!$watchlist) {
            return $this->jsonResponse(
                message: 'Watchlist not found',
                status: 404
            );
        }

       $watchlist->update($validated);

        return $this->jsonResponse(
            message: 'Watchlist updated',
            data: $watchlist
        );
    }

    public function destroy(string $uid): JsonResponse
    {
        $watchlist = $this->getUser()->watchlists()
            ->where('uid', $uid)->first();

        if (!$watchlist) {
            return $this->jsonResponse(
                message: 'Watchlist not found',
                status: 404
            );
        }

        $watchlist->delete();

        return $this->jsonResponse(
            message: 'Watchlist deleted'
        );
    }

    /** Watchlist Items */

    public function addItem(Request $request, string $uid): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string'],
            'poster' => ['nullable', 'string'],
            'media_id' => ['required', 'numeric'],
            'type' => ['required', 'in:movie,tv-show'],
            'release_date' => ['sometimes', 'nullable', 'date'],
        ]);

        $watchlist = $this->getUser()->watchlists()
            ->where('uid', $uid)->first();

        if (!$watchlist) {
            return $this->jsonResponse(
                message: 'Watchlist not found',
                status: 404
            );
        }

        // Find or create media record using provided details
        $media = Media::updateOrCreate(
            [
                'media_id' => $validated['media_id'],
                'type' => $validated['type'],
            ],
            [
                'title' => $validated['title'],
                'poster' => $validated['poster'],
                'release_date' => $validated['release_date'] ?? null,
                'last_synced_at' => now(),
            ]
        );

        // Add to watchlist with notes if provided
        $pivotData = [
            'added_at' => now(),
        ];

        $watchlist->media()->syncWithoutDetaching([$media->id => $pivotData]);

        return $this->jsonResponse(
            message: 'Item added to watchlist',
            data: $media
        );
    }

    public function removeItem(Request $request, string $uid): JsonResponse
    {
        $request->validate([
            'media_id' => ['required', 'integer'],
            'type' => ['required', 'in:movie,tv'],
        ]);

        $watchlist = $this->getUser()->watchlists()
            ->where('uid', $uid)->first();

        if (!$watchlist) {
            return $this->jsonResponse(
                message: 'Watchlist not found',
                status: 404
            );
        }

        // get media
        $media = Media::where('media_id', $request->media_id)
            ->where('type', $request->type)
            ->firstOrFail();

        $watchlist->media()->detach($media->id);

        return $this->jsonResponse(
            message: 'Item removed from watchlist'
        );
    }


    /**
     * Get trending public watchlists.
     */
    public function trending(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $days = $request->input('days', 7);

        $watchlists = Watchlist::whereVisibility('public')
            ->where('updated_at', '>=', now()->subDays($days))
            ->orderBy('view_count', 'desc')
            ->limit($limit)
            ->get();

        return response()->json($watchlists);
    }

    /**
     * Get popular public watchlists.
     */
    public function popular(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);

        $watchlists = Watchlist::whereVisibility('public')
            ->orderBy('view_count', 'desc')
            ->limit($limit)
            ->get();

        return response()->json($watchlists);
    }
}
