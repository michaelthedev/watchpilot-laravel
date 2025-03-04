<?php

namespace App\Interfaces\Providers;

use App\DTO\MovieDetail;
use App\DTO\ShowDetail;

interface MediaProviderI
{
	public function setPage(int $page): self;

	public function getFeatured(string $type = 'all'): array;

	public function getTrending(string $type = 'all'): array;

    public function getTrendingMoviesAndShows(): array;

    public function getMovieDetails(int $id): MovieDetail;

    public function getShowDetails(int $id): ShowDetail;

	public function getWatchProviders(string $type, int $id): array;

    /**
     * @param string $query What to search for
     * @param string $type Search a tv show, movie or all
     * @return array
     */
    public function search(string $query, string $type): array;
}
