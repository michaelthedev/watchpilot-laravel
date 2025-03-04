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
        $search = $this->mediaService->search($request->term);

        return $this->jsonResponse(
            message: 'Search results',
            data: []
        );
    }

    public function trending(Request $request, ?string $type = null): JsonResponse
    {
        return $this->jsonResponse(
            message: 'Trending results',
            data: []
        );
    }

    public function airing(Request $request, ?string $type = null): JsonResponse
    {
        return $this->jsonResponse(
            message: 'Airing results',
            data: []
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
