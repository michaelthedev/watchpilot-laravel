<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Recommendations\RecommendationStrategyI;

final class RecommendationService
{
    public function __construct(
        private readonly RecommendationStrategyI $strategy
    ) {}

    public function recommend(array $mediaIds): array
    {
        return $this->strategy->recommend($mediaIds);
    }
}
