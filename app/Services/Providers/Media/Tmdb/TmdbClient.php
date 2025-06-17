<?php

declare(strict_types=1);

namespace App\Services\Providers\Media\Tmdb;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class TmdbClient
{
    protected PendingRequest $http;

    final protected function _getAccountId(): string
    {
        return config('tmdb.account_id');
    }

    final protected function getClient(int $v = 3): PendingRequest
    {
        return $this->http = Http::withHeaders([
            'Authorization' => 'Bearer '.config('tmdb.api_key'),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->throw()->baseUrl(config('tmdb.base_url')."/{$v}/");
    }
}
