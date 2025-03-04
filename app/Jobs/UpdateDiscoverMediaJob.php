<?php

namespace App\Jobs;

use App\Services\MediaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class UpdateDiscoverMediaJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly MediaService $mediaService
    ) {}

    public function handle(): void
    {
        $this->mediaService->clearCache()
            ->getFeatured();

    }
}
