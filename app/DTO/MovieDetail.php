<?php

declare(strict_types=1);

namespace App\DTO;

final class MovieDetail extends BaseDTO
{
	public string $type = 'movie';

    public function __construct(
        public int $id,
        public string $title,
        public string $imdb_id,
        public string $overview,
        public float $rating,
        public ?string $imageUrl,
        public int $releaseYear,
        public string $releaseDate,
		public array $trailers = [],
        public ?string $backdropUrl = null,
        public ?string $tagline = null,
        public int $runtime = 0,
        public array $genres = [],
    ) {
        $this->rating = round($this->rating, 2);
    }
}
