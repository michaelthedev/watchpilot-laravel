<?php

declare(strict_types=1);

namespace App\Services\Providers\Media\Trakt;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class TraktServiceProvider
{
    private PendingRequest $http;

    public function __construct() {
        // set http client with throw on failure
        $this->http = Http::withHeaders([
            'trakt-version' => '2',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => 'Logad/1.0.0',
            'trakt-api-key' => config('trakt.client_id'),
        ])->throw()->baseUrl('https://api.trakt.tv/');
    }

    public function getTrendingLists(): array
    {
        $response = $this->http->get('lists/trending');

        return $response->json();
    }
}
