<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * Tv Show Data Transfer Object
 * @package App\DTO
 * @author Michael A. <michael@logad.net>
 */
final class TvShowDetail extends BaseDTO
{
	public string $type = 'tv-show';

	public function __construct(
        public int $id,
        public string $title,
        public string $overview,
        public array $seasons,
        public float $rating,
        public string $imageUrl,
        public string $releaseYear,
        public string $releaseDate,
		public int $seasons_count,
        public ?string $backdropUrl = null,
		public array $trailers = [],
        public ?string $tagline = null,
        public ?string $status = null,
        public int $runtime = 0,
        public array $genres = [],
        public ?TvEpisode $lastEpisode = null,
        public ?TvEpisode $nextEpisode = null,
    ) {
        $this->rating = round($this->rating, 2);
    }
}
