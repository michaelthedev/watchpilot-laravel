<?php

namespace App\Interfaces\Providers;

use App\DTO\MovieDetail;
use App\DTO\TvSeason;
use App\DTO\TvShowDetail;

interface MediaProviderI
{
	public function setPage(int $page): self;

	public function getFeatured(string $type = 'all'): array;

	public function getTrending(string $type = 'all'): array;

    public function getTrendingMoviesAndShows(): array;

    public function getMovieDetails(int $id): MovieDetail;

    public function getShowDetails(int $id): TvShowDetail;

    public function getReviews(string $type, int $id): array;

    public function getRelated(string $type, int $id): array;

    public function getSeason(int $id, int $number): TvSeason;

	public function getWatchProviders(string $type, int $id, ?string $region = null): array;

    /**
     * @param string $query What to search for
     * @param string $type Search a tv show, movie or all
     * @return array
     */
    public function search(string $query, string $type): array;
}
