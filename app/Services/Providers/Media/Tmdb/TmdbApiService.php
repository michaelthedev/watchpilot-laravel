<?php

declare(strict_types=1);

namespace App\Services\Providers\Media\Tmdb;

use App\DTO\MovieDetail;
use App\DTO\ShowDetail;
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
	private PendingRequest $http;
	private int $page = 1;

	public function __construct(
        private readonly TmdbTransformer $transformer
    ) {
        // set http client
        $this->http = Http::withHeaders([
            'Authorization' => 'Bearer '.config('tmdb.api_key'),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->baseUrl(config('tmdb.base_url').'/');
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
        $featured = [];
		$request = $this->http->get('discover/movie', [
            'with_original_language' => 'en',
            'sort_by' => 'popularity.desc',
            'with_release_type' => '2|3'
		]);

        if (! $request->successful()) {
            $request->throw();
        }

        $response = $request->json();
        foreach ($response['results'] as $result) {
			$featured[] = $this->transform($result)
				->to('movieSummary');
        }

        return $featured;
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    private function getFeaturedShows(): array
    {
        $featured = [];
		$request = $this->http->get('discover/tv', [
            'with_original_language' => 'en',
            'sort_by' => 'popularity.desc'
		]);

        if (! $request->successful()) {
            $request->throw();
        }

        $response = $request->json();
        foreach ($response['results'] as $result) {
			$featured[] = $this->transform($result)
				->to('tvSummary');
        }

        return $featured;
    }

	public function getTrending(string $type = 'all'): array
	{
		return match ($type) {
			'all' => $this->getTrendingMoviesAndShows(),
			'movies' => $this->getTrendingMovies(),
			'shows' => $this->getTrendingShows(),
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
        $trending = [];
		$request = $this->http->get('trending/movie/'.$period, [
			'query' => [
				'page' => $this->page,
				'with_original_language' => 'en'
			]
		]);

        $response = $request->json();

        foreach ($response['results'] as $result) {
			$trending[] = $this->transformer
				->transform($result)
				->to('movieSummary');
        }

        return $trending;
    }

    private function getTrendingShows(string $period = 'day'): array
    {
        $trending = [];
		$request = $this->http->get('trending/tv/'.$period, [
			'query' => [
				'page' => $this->page,
				'with_original_language' => 'en'
			]
		]);

        $response = json_decode($request->getBody()->getContents(), true);
        foreach ($response['results'] as $result) {
			$trending[] = $this->transformer
				->transform($result)
				->to('tvSummary');
        }

        return $trending;
    }

	private function getAiringShows(string $timezone): array
    {
        $aring = [];

		// get date based on timezone
		$date = Carbon::now($timezone);

		// get beginning and end of week
		$beginningOfWeek = $date->startOfWeek()
			->format('Y-m-d');
		$endOfWeek = $date->endOfWeek()
			->format('Y-m-d');

		$request = $this->http->get('discover/tv', [
			'query' => [
				'air_date.gte' => $beginningOfWeek,
				'air_date.lte' => $endOfWeek,
				'sort_by' => 'popularity.desc',
				'with_original_language' => 'en',
				'timezone' => $timezone
			]
		]);

        $response = json_decode($request->getBody()->getContents(), true);
        foreach ($response['results'] as $result) {
            $aring[] = $this->transformer
				->transform($result)
				->to('tvSummary');
        }

        return $aring;
    }

	private function getAiringMovies(string $timezone): array
    {
        $aring = [];

		// get date based on timezone
		$date = Carbon::now($timezone);

		// get beginning and end of week
		$beginningOfWeek = $date->startOfWeek()->format('Y-m-d');
		$endOfWeek = $date->endOfWeek()->format('Y-m-d');

		$request = $this->http->get('discover/movie', [
			'query' => [
				'air_date.gte' => $beginningOfWeek,
				'air_date.lte' => $endOfWeek,
				'sort_by' => 'popularity.desc',
				'with_original_language' => 'en',
				'timezone' => $timezone
			]
		]);

        $response = json_decode($request->getBody()->getContents(), true);
        foreach ($response['results'] as $result) {
			$aring[] = $this->transformer
				->transform($result)
				->to('movieSummary');
        }

        return $aring;
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
     * @throws Exception|GuzzleException
	 */
    public function getMovieDetails(int $id): MovieDetail
    {
		$request = $this->http->get('movie/'.$id, [
			'query' => [
				'append_to_response' => 'videos'
			]
		]);

        $response = json_decode($request->getBody()
			->getContents(), true);

		return $this->transformer
			->transform($response)
			->to('movie');
    }

	public function getWatchProviders(string $type, int $id): array
	{
		// $request = $this->client->get($type .'/'. $id .'/watch/providers');

		// $response = json_decode($request->getBody()
			// ->getContents(), true);

		return [];
	}

	public function getRelated(string $type, int $id): array
	{
		$request = $this->http->get($type .'/'. $id .'/recommendations', [
			'query' => [
				'language' => 'en-US'
			]
		]);

		$response = json_decode($request->getBody()
			->getContents(), true);

		$related = [];
		foreach ($response['results'] as $result) {
			$related[] = $this->transformer
				->transform($result)
				->to($type.'Summary');
		}

		return $related;
	}

    public function getShowDetails(int $id): ShowDetail
    {
		$request = $this->http->get('tv/'.$id, [
			'query' => [
				'append_to_response' => 'videos'
			]
		]);

        $response = json_decode($request->getBody()
			->getContents(), true);

		return $this->transformer
			->transform($response)
			->to('tv');
    }

	public function getSeason(int $id, int $number): TvSeason
	{
		$request = $this->http->get("tv/$id/season/$number", [
			'query' => [
				'append_to_response' => 'videos'
			]
		]);

		$response = json_decode($request->getBody()
			->getContents(), true);

		return $this->transformer
			->transform($response)
			->to('season');
	}

    public function search(string $query, string $type): array
    {
        $type = match ($type) {
            'all' => 'multi',
            default => $type,
        };

		$request = $this->http->get('search/'.$type, [
			'query' => [
				'query' => $query
			]
		]);

        $response = json_decode($request->getBody()->getContents(), true);
        $results = [];
        foreach ($response['results'] as $result) {
            if (empty($result['poster_path'])) continue;
            $results[] = [
                'id' => $result['id'],
                'type' => $result['media_type'] ?? $type,
                'title' => $result['title'] ?? $result['name'],
                'overview' => substr($result['overview'], 30),
                'rating' => $result['vote_average'],
                // 'imageUrl' => $this->formatImageUrl($result['poster_path']),
                'releaseYear' =>  date('Y', strtotime($result['release_date'] ?? $result['first_air_date']))
            ];
        }

        return $results;
    }

    private function transform(mixed $data): TmdbTransformer
    {
        return $this->transformer->transform($data);
    }
}
