<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use JsonSerializable;

abstract class BaseController
{
    final public function getUser(): ?User
    {
        return auth('api')->user();
    }

    /**
     * General Response for API
     * @param string $message
     * @param array|JsonSerializable|null $data
     * @param int $status
     * @return JsonResponse
     */
    final public function jsonResponse(
        string $message,
        null|array|JsonSerializable $data = null,
        int $status = 200
    ): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $status);
    }
}
