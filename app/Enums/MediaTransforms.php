<?php

declare(strict_types=1);

namespace App\Enums;

enum MediaTransforms: string
{
    case MovieSummary = 'movieSummary';
    case TvSummary = 'tvSummary';
}
