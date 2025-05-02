<?php

declare(strict_types=1);

namespace App\Services\Providers\Media\Tmdb;

use App\DTO\MovieDetail;
use App\DTO\TvShowDetail;
use App\DTO\TvSeason;
use App\Interfaces\Providers\MediaProviderI;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class TmdbApiService implements MediaProviderI
{
	private int $page = 1;
	private PendingRequest $http;

	public function __construct(
        private readonly TmdbTransformer $transformer
    ) {
        // set http client with throw on failure
        $this->http = Http::withHeaders([
            'Authorization' => 'Bearer '.config('tmdb.api_key'),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->throw()->baseUrl(config('tmdb.base_url').'/');
    }

	public function getProviderName(): string
	{
		return 'tmdb';
	}

	public function setPage(int $page): self
	{
		$this->page = $page;
		return $this;
	}

    /**
     * @throws ConnectionException
     */
    public function getFeatured(string $type = 'all'): array
    {
        return match ($type) {
            'movies' => $this->getFeaturedMovies(),
            'shows' => $this->getFeaturedShows(),
            default => $this->getFeaturedMoviesAndShows()
        };
    }

    /**
     * @throws ConnectionException
     */
    public function getFeaturedMoviesAndShows(): array
    {
        return [
            'movies' => $this->getFeaturedMovies(),
            'shows' => $this->getFeaturedShows()
        ];
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    private function getFeaturedMovies(): array
    {
		$request = $this->http->get('discover/movie', [
            'with_original_language' => 'en',
            'sort_by' => 'popularity.desc',
            'with_release_type' => '2|3'
		]);

		return $this->transformResults(
			$request->json('results'), 'movieSummary'
		);
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    private function getFeaturedShows(): array
    {
		$request = $this->http->get('discover/tv', [
            'with_original_language' => 'en',
            'sort_by' => 'popularity.desc'
		]);

		return $this->transformResults(
			$request->json('results'), 'tvSummary'
		);
    }

	public function getTrending(string $type = 'all'): array
	{
		return match ($type) {
			'all' => $this->getTrendingMoviesAndShows(),
			'movies' => $this->getTrendingMovies(),
			'tv-shows' => $this->getTrendingShows(),
			default => []
		};
	}

    public function getTrendingMoviesAndShows(): array
    {
        return [
            'movies' => $this->getTrendingMovies(),
            'shows' => $this->getTrendingShows()
        ];
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    private function getTrendingMovies(string $period = 'day'): array
    {
		$request = $this->http->get('trending/movie/'.$period, [
            'page' => $this->page,
            'with_original_language' => 'en'
		]);

		return $this->transformResults(
			$request->json('results'), 'movieSummary'
		);
    }

    private function getTrendingShows(string $period = 'day'): array
    {
		$request = $this->http->get('trending/tv/'.$period, [
            'page' => $this->page,
            'with_original_language' => 'en'
		]);

		return $this->transformResults(
			$request->json('results'), 'tvSummary'
		);
    }

	private function getAiringShows(string $timezone): array
    {
		$date = Carbon::now($timezone);
		$beginningOfWeek = $date->startOfWeek()->format('Y-m-d');
		$endOfWeek = $date->endOfWeek()->format('Y-m-d');

		$request = $this->http->get('discover/tv', [
            'air_date.gte' => $beginningOfWeek,
            'air_date.lte' => $endOfWeek,
            'sort_by' => 'popularity.desc',
            'with_original_language' => 'en',
            'timezone' => $timezone
		]);

		return $this->transformResults(
			$request->json('results'), 'tvSummary'
		);
    }

	private function getAiringMovies(string $timezone): array
    {
		$date = Carbon::now($timezone);
		$beginningOfWeek = $date->startOfWeek()->format('Y-m-d');
		$endOfWeek = $date->endOfWeek()->format('Y-m-d');

		$request = $this->http->get('discover/movie', [
            'air_date.gte' => $beginningOfWeek,
            'air_date.lte' => $endOfWeek,
            'sort_by' => 'popularity.desc',
            'with_original_language' => 'en',
            'timezone' => $timezone
		]);

		return $this->transformResults(
			$request->json('results'), 'movieSummary'
		);
    }

	public function getAiring(string $timezone): array
	{
		return [
			'movies' => $this->getAiringMovies($timezone),
			'shows' => $this->getAiringShows($timezone),
		];
	}

    /**
     * Get details about a movie
     * @param int $id
     * @return MovieDetail
     * @throws Exception
	 */
    public function getMovieDetails(int $id): MovieDetail
    {
		$request = $this->http->get('movie/'.$id, [
			'query' => [
				'append_to_response' => 'videos'
			]
		]);

		return $this->transformer
			->transform($request->json())
			->to('movie');
    }

    /**
     * @throws ConnectionException
     */
    public function getWatchProviders(string $type, int $id, ?string $region = null): array
	{
        if ($type == 'tv-show') $type = 'tv';

		$request = $this->http->get("$type/$id/watch/providers", [
            'watch_region' => $region
        ]);

        $this->transformResults(
            $request->json('results'), 'watchProviders'
        );
        dd($request->json());

		return [];
	}

    /**
     * @throws ConnectionException
     */
    public function getRelated(string $type, int $id): array
	{
        $type = match ($type) {
            'movie' => 'movie',
            default => 'tv'
        };

		$request = $this->http->get("$type/$id/recommendations", [
            'language' => 'en-US'
		]);

        return $this->transformResults(
            $request->json('results'),
            $type === 'movie' ? 'movieSummary' : 'tvSummary'
        );
	}

    public function getShowDetails(int $id): TvShowDetail
    {
		$request = $this->http->get('tv/'.$id, [
            'append_to_response' => 'videos'
		]);

		return $this->transformer
			->transform($request->json())
			->to('tv');
    }

    /**
     * @throws ConnectionException
     */
    public function getSeason(int $id, int $number): TvSeason
	{
		$request = $this->http->get("tv/$id/season/$number", [
            'append_to_response' => 'videos'
		]);

		return $this->transformer
			->transform($request->json())
			->to('season');
	}

    public function search(string $query, string $type): array
    {
        $type = match ($type) {
            'all' => 'multi',
            'tv-show' => 'tv',
            default => $type,
        };

		$request = $this->http->get('search/'.$type, [
            'query' => $query,
            'language' => 'en-Us',
            'page' => $this->page,
            'include_adult' => false
		]);

        $data = $request->collect('results')
            // filter where media type is movie or tv and backdrops exist
            ->when($type === 'multi', function ($collection) {
                return $collection->filter(fn ($result) => in_array($result['media_type'], ['movie', 'tv']));
            })
            ->filter(fn ($result) => !empty($result['backdrop_path']) || !empty($result['poster_path']))
            ->sortByDesc('popularity')
            ->values()->all();

        return $this->transformResults($data, "searchResult", $type);
    }

	private function transformResults(array $data, string $type, ?string $option = null): array
	{
		return array_map(
			fn ($result) => $this->transformer
				->transform($result)->to($type, $option),
			$data
		);
	}
}
