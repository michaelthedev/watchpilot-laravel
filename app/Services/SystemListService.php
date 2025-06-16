<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MediaTransforms;
use App\Models\SystemList;
use App\Services\Providers\Media\Tmdb\TmdbClient;
use App\Services\Providers\Media\Tmdb\TmdbTransformer;
use Illuminate\Support\Facades\File;

final class SystemListService extends TmdbClient
{
    public function __construct(
        private readonly TmdbTransformer $transformer
    ) {}

    private function getLists(): array
    {
        return File::json(resource_path('json/tmdb_auto_lists.json'));
    }

    public function process(): void
    {
        $lists = $this->getLists();

        foreach ($lists as $list) {
            $response = $this->getClient()->get($list['endpoint']);

            $data = $response->json('results');
            if (empty($data)) continue;

            $transform = match($list['type']) {
                'movie' => MediaTransforms::MovieSummary,
                'tv-show' => MediaTransforms::TvSummary,
                default => null
            };

            $formed = $this->transformResults($data, $transform?->value);

            SystemList::upsert([
                'name' => $list['name'],
                'slug' => $list['slug'],
                'type' => $list['type'],
                'items' => json_encode($formed),
                'poster' => $formed[0]['imageUrl'],
                'description' => $list['description'],
            ], ['slug']);
        }
    }

    public function getCuratedLists(): array
    {
        return cache()->remember("systemLists.curated", 3600, function() {
            $accountId = $this->_getAccountId();
            $response = $this->getClient(4)->get("account/$accountId/lists");

            return $this->transformResults(
                $response->json('results'),
                'customList'
            );
        });
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
