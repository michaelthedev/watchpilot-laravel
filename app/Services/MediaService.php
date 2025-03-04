<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\Providers\MediaProviderI;
use App\Services\Providers\Media\Tmdb\TmdbApiService;

final class MediaService
{

    public function clearCache(): self
    {
        cache()->forget('discover.featured.all');
        cache()->forget('discover.featured.movies');
        cache()->forget('discover.featured.shows');

        cache()->forget('discover.trending.all');
        cache()->forget('discover.trending.movies');
        cache()->forget('discover.trending.shows');

        cache()->forget('discover.airing.all');
        cache()->forget('discover.airing.movies');
        cache()->forget('discover.airing.shows');

        return $this;
    }

    public function search(): array
    {
        return [];
    }

    public function trending(): array
    {
        return [];
    }

    public function airing(): array
    {
        return [];
    }

    public function getFeatured(string $type = 'all'): array
    {
        $expiry = now()->startOfDay()->addDay();

        /*try {

        } catch (\Exception $e) {

        }*/
        return cache()->remember('discover.featured'.$type, $expiry,
            function() use ($type) {
                return $this->getProvider()->getFeatured($type);
            }
        );
    }

    private function getProvider(): MediaProviderI
    {
        return app(TmdbApiService::class);
    }
}
