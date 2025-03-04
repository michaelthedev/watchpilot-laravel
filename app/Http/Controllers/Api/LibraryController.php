<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LibraryController extends BaseController
{
    public function likes(): JsonResponse
    {
        return $this->jsonResponse(
            message: 'success',
            data: $this->getUser()->likes()->paginate()
        );
    }

    public function addLike(Request $request): JsonResponse
    {
        $request->validate([
            'media_id' => ['required']
        ]);

        $like = $this->getUser()->likes()->create([
            'media_id' => $request->media_id
        ]);

        return $this->jsonResponse(
            status: 201,
            message: 'Like added',
            data: $like
        );
    }

    public function removeLike(Request $request): JsonResponse
    {
        $request->validate([
            'media_id' => ['required']
        ]);

        $this->getUser()->likes()
            ->where('media_id', $request->media_id)
            ->delete();

        return $this->jsonResponse(
            message: 'Like removed'
        );
    }

    public function watching(): JsonResponse
    {
        return $this->jsonResponse(
            message: 'success',
            data: $this->getUser()->watching()->paginate()
        );
    }
}
