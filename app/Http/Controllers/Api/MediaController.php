<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Media;
use App\Rules\MediaRule;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MediaController extends BaseController
{
    public function __construct(
        private readonly MediaService $service
    ) {}

    public function show(string $type, int $id): JsonResponse
    {
        if ($type == "movie") {
            $result = $this->service->getMovieDetails($id);
        } else {
            $result = $this->service->getShowDetails($id);
        }

        return $this->jsonResponse(
            message: 'media details',
            data: $result?->toArray()
        );
    }

    public function reviews(string $type, int $id): JsonResponse
    {
        $result = $this->service->getReviews($type, $id);

        return $this->jsonResponse(
            message: 'media reviews',
            data: $result
        );
    }

    public function addReview(Request $request, string $type, int $id): JsonResponse
    {
        $user = $this->getUser();
        if (! $user) {
           return $this->jsonResponse(
                message: 'You must be logged in to add a review',
                status: 401
            );
        }

        $validated = $request->validate([
            'media' => ['required', 'array', new MediaRule()],
            'content' => ['required', 'string', 'min:30'],
        ]);

        // Find or create media record using provided details
        $media = Media::upsertItem($validated['media']);

        // Check if the user has already reviewed this media
        if ($user->reviews()->where('media_id', $media->id)->exists()) {
            return $this->jsonResponse(
                message: 'You already have a review for this media',
                status: 400
            );
        }

        $user->reviews()->create([
            'media_id' => $media->id,
            'content' => $validated['content'],
            'rating' => $request->rating ?? 0
        ]);

        return $this->jsonResponse(
            status: 201,
            message: 'Review submitted',
        );
    }

    public function related(string $type, int $id): JsonResponse
    {
        $result = $this->service->getRelated($type, $id);

        return $this->jsonResponse(
            message: 'related media',
            data: $result
        );
    }

    public function providers(Request $request, string $type, int $id): JsonResponse
    {
        $result = $this->service->getWatchProviders($type, $id, $request->region);

        return $this->jsonResponse(
            message: 'providers',
            data: $result
        );
    }

    public function seasons(string $type, int $id, int $number): JsonResponse
    {
        return $this->jsonResponse(
            message: 'season data',
            data: $this->service->getSeason($id, $number)?->toArray()
        );
    }
}
