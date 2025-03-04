<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MiscController extends BaseController
{
    public function timezones(Request $request): JsonResponse
    {
        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        return $this->jsonResponse(
            message: 'Timezones',
            data: $timezones
        );
    }
}
