<?php

declare(strict_types=1);

namespace App\Services\Providers\Media\Tmdb;

use App\DTO\MovieDetail;
use App\DTO\TvShowDetail;
use App\DTO\TvEpisode;
use App\DTO\TvSeason;
use DateTimeImmutable;
use Exception;
use Illuminate\Support\Str;

final class TmdbTransformer
{
	private array $data;

	public function transform(array $data): self
	{
		$this->data = $data;
		return $this;
	}

    /**
     * @throws Exception
     */
    public function to(string $type, ?string $option = null): MovieDetail|array|TvShowDetail|TvSeason
	{
		return match ($type) {
			'movie' => $this->transformMovie($this->data),
			'tv' => $this->transformTv($this->data),
			'season' => $this->transformSeason($this->data),
			'movieSummary' => $this->transformMovieSummary($this->data),
			'tvSummary' => $this->transformTvSummary($this->data),
            'searchResult' => $this->transformSearchResult($this->data, $option),
            // 'watchProvider' => $this->transform(),
            'review' => $this->transformReview($this->data),

            'customList' => $this->transformCustomList($this->data),
			default => throw new Exception('Invalid type')
		};
	}

	private function transformMovie(array $data): MovieDetail
	{
		return new MovieDetail(
			id: $data['id'],
			title: $data['title'],
            imdb_id: $data['imdb_id'],
			genres: $data['genres'],
			rating: $data['vote_average'],
			runtime: $data['runtime'],
			tagline: $data['tagline'],
			trailers: $this->findTrailerFromVideos($data['videos']['results'] ?? []),
			overview: $data['overview'],
			imageUrl: $this->formatImageUrl($data['poster_path']),
			releaseDate: $data['release_date'],
			backdropUrl: $this->formatImageUrl($data['backdrop_path'], "high"),
			releaseYear: (int) $this->formatReleaseDate($data['release_date'])
		);
	}

	private function transformMovieSummary(array $data): array
	{
		return [
			'id' => $data['id'],
			'type' => 'movie',
			'title' => htmlentities($data['title']),
			'overview' => htmlentities($data['overview']),
			'rating' => round($data['vote_average'], 2, PHP_ROUND_HALF_DOWN),
			'imageUrl' => $this->formatImageUrl($data['poster_path']),
			'releaseYear' =>  $this->formatReleaseDate($data['release_date']),
		];
	}

	private function transformTv(array $data): TvShowDetail
	{
		return new TvShowDetail(
			id: $data['id'],
			title: $data['name'],
            imdb_id: $data['external_ids']['imdb_id'] ?? null,
			genres: $data['genres'],
			rating: $data['vote_average'],
			status: $data['status'],
			runtime: $data['episode_run_time'][0] ?? 0,
			seasons_count: $data['number_of_seasons'],
			seasons: $this->getSeasons($data['seasons']),
			trailers: $this->findTrailerFromVideos($data['videos']['results'] ?? []),
			tagline: $data['tagline'],
			overview: $data['overview'],
			imageUrl: $this->formatImageUrl($data['poster_path']),
			releaseDate: $data['first_air_date'],
			backdropUrl: $this->formatImageUrl($data['backdrop_path'], "high"),
			releaseYear: $this->formatReleaseDate($data['first_air_date']),
			lastEpisode: $this->getEpisodeDto($data['last_episode_to_air']),
			nextEpisode: $this->getEpisodeDto($data['next_episode_to_air']),
		);
	}

	private function transformTvSummary(array $data): array
	{
		return [
			'id' => $data['id'],
			'type' => 'tv-show',
			'title' => htmlentities($data['name']),
			'overview' => htmlentities($data['overview'], 30),
			'rating' => round($data['vote_average'], 2, PHP_ROUND_HALF_DOWN),
			'imageUrl' => $this->formatImageUrl($data['poster_path']),
			'releaseYear' =>  $this->formatReleaseDate($data['first_air_date']),
		];
	}

	private function transformSeason(array $data): TvSeason
	{
		$episodes = [];
		foreach ($data['episodes'] as $episode) {
			$episodes[] = $this->getEpisodeDto($episode);
		}

		if (!empty($data['videos']['results'])) {
			$trailers = $this->findTrailerFromVideos($data['videos']['results']);
		}

		return new TvSeason(
			id: $data['id'],
			title: $data['name'],
			number: $data['season_number'],
			rating: $data['vote_average'],
			episodes: $episodes,
			trailers: $trailers ?? [],
			imageUrl: $this->formatImageUrl($data['poster_path']),
			overview: $data['overview'],
			releaseDate: $data['air_date'],
		);
	}

    private function transformSearchResult(array $data, ?string $option = null): array
    {
        $type = match ($data['media_type'] ?? $option) {
            'tv' => 'tv-show',
            default => 'movie',
        };

        $date = $data['release_date'] ?? $data['first_air_date'] ?? null;

        return [
            'id' => $data['id'],
            'type' => $type,
            'title' => $data['title'] ?? $data['name'],
            'overview' => $data['overview'],
            'rating' => round($data['vote_average'], 1),
            'imageUrl' => $this->formatImageUrl($data['poster_path'] ?? $data['backdrop_path']),
            'releaseYear' => $date ? date('Y', strtotime($date)) : '0000'
        ];
    }

    private function transformReview(array $data): array
    {
        return [
            'source' => 'provider',
            'author' => $data['author'],
            'summary' => strip_tags(Str::limit($data['content'], 200)),
            'content' => $data['content'],
            'date' => $data['updated_at'],
        ];
    }

    private function transformCustomList(array $data): array
    {
        return [
            'id' => $data['id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'number_of_items' => $data['number_of_items'],
            'imageUrl' => $this->formatImageUrl($data['poster_path'] ?? $data['backdrop_path'], "medium"),
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
        ];
    }

	private function getSeasons(array $seasons): array
	{
		$data = [];
		foreach ($seasons as $season) {
			$data[] = new TvSeason(
				id: (int) $season['id'],
				title: $season['name'],
				number: $season['season_number'],
				rating: $season['vote_average'],
				imageUrl: $this->formatImageUrl($season['poster_path']),
				overview: $season['overview'],
				releaseDate: $season['air_date'],
			);
		}

		return $data;
	}

	/**
	 * @param ?array $episode
	 * @return TvEpisode|null
	 */
	private function getEpisodeDto(?array $episode): ?TvEpisode
	{
		if (empty($episode)) {
			return null;
		}

		return new TvEpisode(
			id: $episode['id'],
			title: $episode['name'],
			rating: $episode['vote_average'],
			season: $episode['season_number'],
			runtime: $episode['runtime'],
			episode: $episode['episode_number'],
			overview: $episode['overview'],
			imageUrl: $this->formatImageUrl($episode['still_path']),
			releaseDate: $episode['air_date'],
		);
	}

    private function formatWatchProvider(array $data): array
    {
        return [];
    }

	private function formatReleaseDate(string $releaseDate, string $format = 'Y'): string
	{
		return (new DateTimeImmutable($releaseDate))
			->format($format);
	}

	public function formatImageUrl(?string $image, ?string $res = null): ?string
	{
		if (empty($image)) return asset('assets/images/dummy-img.png');

        $resolution = match($res) {
            'medium' => 'w1280',
            'high' => 'original',
            default => 'w500',
        };

		return 'https://image.tmdb.org/t/p/' .$resolution. $image;
	}

	private function findTrailerFromVideos(array $videos): array
	{
		$trailers = [];
		foreach ($videos as $video) {
			if ($video['type'] == 'Trailer') {
				$trailers[] = [
					'key' => $video['key'],
					'name' => $video['name'],
					'site' => $video['site'],
				];
			}
		}

		return $trailers;
	}
}
