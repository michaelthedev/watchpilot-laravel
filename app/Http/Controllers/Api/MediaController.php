<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

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

    public function related(string $type, int $id): JsonResponse
    {
        $result = $this->service->getRelated($type, $id);

        return $this->jsonResponse(
            message: 'related media',
            data: $result
        );
    }

    public function providers(string $type, int $id): JsonResponse
    {
        $result = $this->service->getProviders($type, $id);

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
