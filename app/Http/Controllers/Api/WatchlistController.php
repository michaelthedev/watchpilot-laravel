<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WatchlistController
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required']
        ]);

        return $this->jsonResponse(
            message: 'Watchlist added',
            data: []
        );
    }
}
