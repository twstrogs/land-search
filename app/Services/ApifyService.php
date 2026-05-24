<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApifyService
{
    private function actorPath(): string
    {
        $actor = Setting::getValue('scraper.apify_actor', 'apify/facebook-groups-scraper');
        return str_replace('/', '~', trim((string) $actor));
    }

    private function actorCandidates(): array
    {
        $stored = $this->actorPath();

        return array_values(array_unique(array_filter([
            $stored,
            'apify~facebook-groups-scraper',
            'apify~facebook-group-scraper',
        ])));
    }

    private function buildPayloadCandidates(string $groupUrl, int $limit): array
    {
        return [
            [
                'startUrls' => [['url' => $groupUrl]],
                'resultsLimit' => $limit,
                'viewOption' => 'CHRONOLOGICAL',
            ],
            [
                'groupUrls' => [$groupUrl],
                'resultsLimit' => $limit,
                'includeComments' => false,
                'includeReactors' => false,
            ],
        ];
    }

    public function scrape(string $groupUrl, string $apiToken, int $limit = 20): array
    {
        try {
            if (empty($apiToken)) {
                Log::error('Apify scrape failed: missing API token');
                return [];
            }
            $http = Http::withToken($apiToken)
                ->acceptJson()
                ->withHeaders(['Content-Type' => 'application/json']);

            $lastError = null;
            $payloads = $this->buildPayloadCandidates($groupUrl, $limit);

            foreach ($this->actorCandidates() as $actorPath) {
                foreach ($payloads as $payload) {
                    // 1) Ưu tiên endpoint đồng bộ trả items trực tiếp.
                    $syncItemsResponse = $http
                        ->timeout(300)
                        ->post("https://api.apify.com/v2/acts/{$actorPath}/run-sync-get-dataset-items", $payload);

                    if ($syncItemsResponse->successful()) {
                        $items = $syncItemsResponse->json();
                        if (is_array($items)) {
                            return $this->transformPosts($items);
                        }
                    }

                    $lastError = [
                        'phase' => 'run-sync-get-dataset-items',
                        'actor' => $actorPath,
                        'payload_keys' => array_keys($payload),
                        'status' => $syncItemsResponse->status(),
                        'body' => $syncItemsResponse->body(),
                    ];

                    // 2) Fallback: run-sync rồi đọc dataset.
                    $runResponse = $http
                        ->timeout(300)
                        ->post("https://api.apify.com/v2/acts/{$actorPath}/run-sync", $payload);

                    if (! $runResponse->successful()) {
                        $lastError = [
                            'phase' => 'run-sync',
                            'actor' => $actorPath,
                            'payload_keys' => array_keys($payload),
                            'status' => $runResponse->status(),
                            'body' => $runResponse->body(),
                        ];
                        continue;
                    }

                    $runResult = $runResponse->json();
                    $datasetId = $runResult['data']['defaultDatasetId'] ?? null;
                    if (! $datasetId) {
                        $lastError = [
                            'phase' => 'dataset-id-missing',
                            'actor' => $actorPath,
                            'payload_keys' => array_keys($payload),
                            'status' => $runResponse->status(),
                            'body' => $runResponse->body(),
                        ];
                        continue;
                    }

                    $itemsResponse = $http
                        ->timeout(120)
                        ->get("https://api.apify.com/v2/datasets/{$datasetId}/items", [
                            'clean' => true,
                            'format' => 'json',
                        ]);

                    if (! $itemsResponse->successful()) {
                        $lastError = [
                            'phase' => 'dataset-items',
                            'actor' => $actorPath,
                            'payload_keys' => array_keys($payload),
                            'dataset_id' => $datasetId,
                            'status' => $itemsResponse->status(),
                            'body' => $itemsResponse->body(),
                        ];
                        continue;
                    }

                    $items = $itemsResponse->json();
                    if (is_array($items)) {
                        return $this->transformPosts($items);
                    }
                }
            }

            Log::error('Apify scrape failed after all strategies', [
                'group_url' => $groupUrl,
                'limit' => $limit,
                'last_error' => $lastError,
            ]);
            return [];

        } catch (\Exception $e) {
            Log::error('Apify service error', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    protected function transformPosts(array $items): array
    {
        return array_map(function ($item) {
            // Extract image URLs from various possible locations
            $imageUrls = $this->extractImageUrls($item);

            return [
                'raw_content' => $item['text'] ?? '',
                'author_name' => $item['authorName'] ?? $item['user']['name'] ?? null,
                'author_id' => $item['authorId'] ?? $item['user']['id'] ?? null,
                'image_url' => $imageUrls[0] ?? null,
                'images' => $imageUrls,
                'facebook_url' => $item['url'] ?? null,
                'published_at' => $item['createdAt'] ?? $item['time'] ?? null,
                'likes' => $item['likes'] ?? $item['likesCount'] ?? 0,
                'comments' => $item['comments'] ?? $item['commentsCount'] ?? 0,
                // ===== TASK 2: Save full Apify JSON =====
                '_apify_raw_json' => $item, // Full raw data from Apify
            ];
        }, $items);
    }

    /**
     * Extract image URLs from various Apify JSON structures
     */
    protected function extractImageUrls(array $item): array
    {
        $urls = [];

        // 1. Direct images array (most common)
        if (!empty($item['images']) && is_array($item['images'])) {
            foreach ($item['images'] as $img) {
                if (is_string($img)) {
                    $urls[] = $img;
                } elseif (is_array($img) && !empty($img['url'])) {
                    $urls[] = $img['url'];
                }
            }
        }

        // 2. Direct image field
        if (!empty($item['image']) && is_string($item['image'])) {
            $urls[] = $item['image'];
        }

        // 3. Nested attachments array (common in newer Apify versions)
        if (!empty($item['attachments']) && is_array($item['attachments'])) {
            foreach ($item['attachments'] as $attachment) {
                // Check for image.uri in nested structure
                if (!empty($attachment['image']['uri'])) {
                    $url = $attachment['image']['uri'];
                    // Skip non-HTTP URLs (like Messenger links)
                    if (str_starts_with($url, 'http')) {
                        $urls[] = $url;
                    }
                }
                // Check for thumbnail
                if (!empty($attachment['thumbnail']) && str_starts_with($attachment['thumbnail'], 'http')) {
                    $urls[] = $attachment['thumbnail'];
                }
            }
        }

        // 4. Media array (some Apify actors use this)
        if (!empty($item['media']) && is_array($item['media'])) {
            foreach ($item['media'] as $media) {
                if (!empty($media['image']['uri']) && str_starts_with($media['image']['uri'], 'http')) {
                    $urls[] = $media['image']['uri'];
                }
            }
        }

        // Remove duplicates while preserving order
        return array_values(array_unique($urls));
    }

    public function checkHealth(string $apiToken): bool
    {
        try {
            $response = Http::withToken($apiToken)
                ->get('https://api.apify.com/v2/users/me');
            
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
