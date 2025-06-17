<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LibraryController extends BaseController
{
    public function likes(): JsonResponse
    {
        return $this->jsonResponse(
            message: 'success',
            data: $this->getUser()->likedMedia()
                ->latest()->paginate()
        );
    }

    public function toggleLike(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string'],
            'poster' => ['nullable', 'string'],
            'media_id' => ['required', 'numeric'],
            'type' => ['required', 'in:movie,tv-show'],
            'release_date' => ['sometimes', 'nullable', 'date'],
        ]);

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

        // Toggle like status
        $liked = false;
        $user = $this->getUser();

        // Check if already liked
        $alreadyLiked = $user->likedMedia()->where('media_id', $media->id)->exists();

        if ($alreadyLiked) {
            $user->likedMedia()->detach($media->id);
        } else {
            $user->likedMedia()->attach($media->id);

            $liked = true;
        }

        return $this->jsonResponse(
            message: 'success',
            data: [
                'liked' => $liked
            ]
        );
    }
}
