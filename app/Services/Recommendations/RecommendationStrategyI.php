<?php

declare(strict_types=1);

namespace App\Services\Recommendations;

interface RecommendationStrategyI
{
    public function recommend(array $mediaIds): array;
}
