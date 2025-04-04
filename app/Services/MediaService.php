<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\MovieDetail;
use App\DTO\TvSeason;
use App\DTO\TvShowDetail;
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

    public function search(string $term, ?string $type = null): array
    {
        $type = $type ?? 'all';
        return $this->getProvider()->search($term, $type);
    }

    public function getTrending(?string $type = null): array
    {
        $type = $type ?? 'all';
        $expiry = now()->addHours(6);

        try {
            return cache()->remember('discover.trending.' . $type, $expiry,
                function () use ($type) {
                    return $this->getProvider()->getTrending($type);
                }
            );
        } catch (\Exception $e) {
            logger()->channel('media')
                ->error('Trending error: ' . $e->getMessage());
            return [];
        }
    }

    public function getAiring(?string $type = null, string $timezone = 'UTC'): array
    {
        $type = $type ?? 'all';
        $expiry = now()->addHours(12);

        try {
            return cache()->remember('discover.airing.' . $type, $expiry,
                function () use ($type, $timezone) {
                    return $this->getProvider()->getAiring($timezone);
                }
            );
        } catch (\Exception $e) {
            logger()->channel('media')
                ->error('Airing error: ' . $e->getMessage());
            return [];
        }
    }

    public function getFeatured(?string $type = null): array
    {
        $type = $type ?? 'all';
        $expiry = now()->startOfDay()->addDay();

        try {
            return cache()->remember('discover.featured.' . $type, $expiry,
                function () use ($type) {
                    return $this->getProvider()->getFeatured($type);
                }
            );
        } catch (\Exception $e) {
            logger()->channel('media')
                ->error('Featured error: ' . $e->getMessage());
            return [];
        }
    }

    public function getShowDetails(int $id): ?TvShowDetail
    {
        try {
            $key = "media.tv.$id";
            $expiry = now()->addHour();

            return cache()->remember($key, $expiry, function() use($id) {
                return $this->getProvider()->getShowDetails($id);
            });
        } catch (\Exception $e) {
            logger()->channel('media')
                ->error('Tv show details error: ' . $e->getMessage());
        }

        return null;
    }

    public function getMovieDetails(int $id): ?MovieDetail
    {
        try {
            $key = "media.movie.$id";
            $expiry = now()->addHour();

            return cache()->remember($key, $expiry, function() use($id) {
                return $this->getProvider()->getMovieDetails($id);
            });
        } catch (\Exception $e) {
            logger()->channel('media')
                ->error('Movie details error: ' . $e->getMessage());
        }

        return null;
    }

    public function getRelated(string $type, int $id): array
    {
        try {
            return $this->getProvider()->getRelated($type, $id);
        } catch (\Exception $e) {
            logger()->channel('media')
                ->error('Related error: ' . $e->getMessage());
            return [];
        }
    }

    public function getSeason(int $media_id, int $number): ?TvSeason
    {
        try {
            return $this->getProvider()->getSeason($media_id, $number);
        } catch (\Exception $e) {
            logger()->channel('media')
                ->error('Seasons error: ' . $e->getMessage());
            return null;
        }
    }

    public function getWatchProviders(string $type, int $id): array
    {

    }

    private function getProvider(): MediaProviderI
    {
        return app(TmdbApiService::class);
    }
}
