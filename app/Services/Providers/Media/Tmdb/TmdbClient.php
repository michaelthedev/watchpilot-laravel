<?php

declare(strict_types=1);

namespace App\Services\Providers\Media\Tmdb;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class TmdbClient
{
    protected PendingRequest $http;
    protected string $account_id;

    public function __construct(bool $v4 = false) {
        $version = $v4 ? '4' : '3';

        $this->account_id = config('tmdb.account_id');

        // set http client with throw on failure
        $this->http = Http::withHeaders([
            'Authorization' => 'Bearer '.config('tmdb.api_key'),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->throw()->baseUrl(config('tmdb.base_url')."/{$version}/");
    }

    protected function getClient(int $v = 3): PendingRequest
    {
        return $this->http = Http::withHeaders([
            'Authorization' => 'Bearer '.config('tmdb.api_key'),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->throw()->baseUrl(config('tmdb.base_url')."/{$v}/");
    }
}
