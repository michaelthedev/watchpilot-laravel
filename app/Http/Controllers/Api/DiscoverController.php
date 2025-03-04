<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DiscoverController extends BaseController
{
    public function __construct(
        private readonly MediaService $mediaService
    ) {}

    public function search(Request $request): JsonResponse
    {
        return $this->jsonResponse(
            message: 'Search results',
            data: $this->mediaService->search($request->term, $request->type)
        );
    }

    public function trending(Request $request, ?string $type = null): JsonResponse
    {
        return $this->jsonResponse(
            message: 'Trending results',
            data: $this->mediaService->getTrending($type)
        );
    }

    public function airing(Request $request, ?string $type = null): JsonResponse
    {
        return $this->jsonResponse(
            message: 'Airing results',
            data: $this->mediaService->getAiring($type)
        );
    }

    public function featured(?string $type = null): JsonResponse
    {
        return $this->jsonResponse(
            message: 'Featured results',
            data: $this->mediaService->getFeatured($type)
        );
    }
}
