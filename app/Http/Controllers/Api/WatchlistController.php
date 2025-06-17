<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Media;
use App\Models\SystemList;
use App\Models\Watchlist;
use App\Services\SystemListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class WatchlistController extends BaseController
{
    public function index(): JsonResponse
    {
        $watchlists = $this->getUser()->watchlists()
            ->withCount('items')
            ->orderBy('name')
            ->get();

        return $this->jsonResponse(
            message: 'User watchlists',
            data: $watchlists
        );
    }

    public function show(string $uid): JsonResponse
    {
        $watchlist = Watchlist::whereUid($uid)
            ->with('items.media')
            ->canBeViewed($this->getUser())
            ->firstOrFail();

        $watchlist->incrementViews();

        return $this->jsonResponse(
            message: 'Watchlist details',
            data: $watchlist
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

    public function items(string $uid): JsonResponse
    {
        $watchlist = Watchlist::whereUid($uid)
            ->canBeViewed($this->getUser())
            ->firstOrFail();

        return $this->jsonResponse(
            message: 'Watchlist items',
            data: $watchlist->items()->with('media')->paginate()
        );
    }

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
                'tmdb_id' => $validated['media_id'],
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
        $media = Media::whereTmdbId($request->media_id)
            ->where('type', $request->type)
            ->first();

        $media && $watchlist->media()->detach($media->id);

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

    public function curated(): JsonResponse
    {
        /** @var SystemListService $systemListService */
        $systemListService = app(SystemListService::class);
        $curatedLists = $systemListService->getCuratedLists();

        return $this->jsonResponse(
            message: 'Curated watchlists',
            data: $curatedLists
        );
    }

    public function automated(Request $request, ?string $slug = null): JsonResponse
    {
        $columns = [
            'slug',
            'name',
            'type',
            'poster',
            'description',
            'created_at',
            'updated_at',
        ];
        if ($slug) {
            $list = SystemList::whereSlug($slug)->firstOrFail();

            return $this->jsonResponse(
                message: 'success',
                data: $list->only($columns) + [
                    'items' => $list->items,
                ]
            );
        } else {
            $autoLists = SystemList::latest()->paginate(
                perPage: $request->input('limit', 10),
                columns: $columns
            )->toArray();

            return $this->jsonResponse(
                message: 'Automated watchlists',
                data: $autoLists
            );
        }
    }
}
