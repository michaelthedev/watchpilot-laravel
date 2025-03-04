<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Watchlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class WatchlistController extends BaseController
{
    public function index(): JsonResponse
    {
        $watchlists = $this->getUser()->watchlists()
            ->with('items')
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

        // Check if watchlist belongs to user or is public
        if ($watchlist->user_id !== $this->getUser()?->id && $watchlist->visibility !== 'public') {
            return $this->jsonResponse(
                message: 'Unauthorized',
                status: 403
            );
        }

        return $this->jsonResponse(
            message: 'Watchlist details',
            data: $watchlist
        );
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'visibility' => ['required', 'in:public,private']
        ]);

        $user = $this->getUser();

        $watchlist = $user->watchlists()->create([
            'name' => $request->name,
            'visibility' => $request->visibility,
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

    public function addItem(Request $request, string $uid): JsonResponse
    {
        $validated = $request->validate([
            'media_id' => ['required', 'integer'],
            'poster' => ['sometimes', 'url'],
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

        $watchlist->items()->create($validated);

        return $this->jsonResponse(
            message: 'Item added to watchlist'
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

        $watchlist->items()
            ->where('media_id', $request->media_id)
            ->where('type', $request->type)
            ->delete();

        return $this->jsonResponse(
            message: 'Item removed from watchlist'
        );
    }
}
