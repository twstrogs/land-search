<?php

namespace App\Search;

use App\Models\Post;
use App\Models\PostEmbedding;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Semantic Search Service
 * 
 * Performs semantic search on posts using embeddings:
 * - Vector similarity search
 * - Hybrid search (semantic + keyword)
 * - Reranking
 */
class SemanticSearchService
{
    private EmbeddingService $embeddingService;
    private array $config;

    public function __construct(?EmbeddingService $embeddingService = null)
    {
        $this->embeddingService = $embeddingService ?? new EmbeddingService();
        $this->config = config('services.search', []);
    }

    /**
     * Search posts semantically
     */
    public function search(
        string $query,
        array $filters = [],
        int $limit = 20,
        float $semanticWeight = 0.6
    ): array {
        // Generate query embedding
        $queryEmbedding = $this->embeddingService->embed($query);

        // Get semantic results
        $semanticResults = $this->semanticSearch($queryEmbedding, $filters, $limit * 2);

        // Get keyword results
        $keywordResults = $this->keywordSearch($query, $filters, $limit * 2);

        // Combine and rerank
        return $this->hybridRerank(
            $semanticResults,
            $keywordResults,
            $query,
            $limit,
            $semanticWeight
        );
    }

    /**
     * Pure semantic search
     */
    public function semanticSearch(array $embedding, array $filters = [], int $limit = 20): array
    {
        try {
            // Get all post embeddings
            $embeddings = PostEmbedding::with('post')
                ->whereNotNull('embedding')
                ->get();

            $results = [];

            foreach ($embeddings as $item) {
                $post = $item->post;
                
                // Skip if post doesn't exist
                if (!$post) {
                    continue;
                }

                // Apply filters
                if (!$this->passesFilters($post, $filters)) {
                    continue;
                }

                // Calculate similarity
                $similarity = $this->embeddingService->cosineSimilarity(
                    $embedding,
                    $item->embedding
                );

                $results[] = [
                    'post_id' => $post->id,
                    'similarity' => $similarity,
                    'post' => $post,
                ];
            }

            // Sort by similarity
            usort($results, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

            return array_slice($results, 0, $limit);

        } catch (\Exception $e) {
            Log::error('[SemanticSearch] Search failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Keyword-based search
     */
    public function keywordSearch(string $query, array $filters = [], int $limit = 20): array
    {
        $posts = Post::query();

        // Apply filters
        $this->applyFilters($posts, $filters);

        // Search in title, description, address
        $posts->where(function ($q) use ($query) {
            $q->where('title', 'LIKE', "%{$query}%")
              ->orWhere('description', 'LIKE', "%{$query}%")
              ->orWhere('address_text', 'LIKE', "%{$query}%");
        });

        // Get results
        $posts = $posts->limit($limit)->get();

        return $posts->map(function ($post) {
            return [
                'post_id' => $post->id,
                'relevance' => 1.0, // Keyword matches are considered fully relevant
                'post' => $post,
            ];
        })->toArray();
    }

    /**
     * Hybrid reranking
     */
    private function hybridRerank(
        array $semanticResults,
        array $keywordResults,
        string $query,
        int $limit,
        float $semanticWeight
    ): array {
        $scores = [];

        // Add semantic scores
        foreach ($semanticResults as $result) {
            $scores[$result['post_id']] = [
                'post_id' => $result['post_id'],
                'post' => $result['post'],
                'semantic_score' => $result['similarity'],
                'keyword_score' => 0,
                'combined_score' => $result['similarity'] * $semanticWeight,
            ];
        }

        // Add keyword scores
        foreach ($keywordResults as $result) {
            if (isset($scores[$result['post_id']])) {
                $scores[$result['post_id']]['keyword_score'] = $result['relevance'];
                $scores[$result['post_id']]['combined_score'] += $result['relevance'] * (1 - $semanticWeight);
            } else {
                $scores[$result['post_id']] = [
                    'post_id' => $result['post_id'],
                    'post' => $result['post'],
                    'semantic_score' => 0,
                    'keyword_score' => $result['relevance'],
                    'combined_score' => $result['relevance'] * (1 - $semanticWeight),
                ];
            }
        }

        // Sort by combined score
        usort($scores, fn($a, $b) => $b['combined_score'] <=> $a['combined_score']);

        return array_slice($scores, 0, $limit);
    }

    /**
     * Index a post
     */
    public function indexPost(Post $post): bool
    {
        try {
            // Build searchable content
            $content = $this->embeddingService->buildSearchableContent($post);

            // Generate embedding
            $embedding = $this->embeddingService->embed($content);

            // Save to database
            PostEmbedding::updateOrCreate(
                ['post_id' => $post->id],
                [
                    'embedding' => $embedding,
                    'embedding_model' => $this->embeddingService->getProviderInfo()['model'],
                ]
            );

            // Clear search cache
            Cache::tags(['search'])->flush();

            return true;

        } catch (\Exception $e) {
            Log::error('[SemanticSearch] Failed to index post', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Index multiple posts
     */
    public function indexBatch(array $postIds, callable $progressCallback = null): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        $posts = Post::whereIn('id', $postIds)->get();

        foreach ($posts as $i => $post) {
            try {
                if ($this->indexPost($post)) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Failed to index post {$post->id}";
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = $e->getMessage();
            }

            if ($progressCallback) {
                $progressCallback($i + 1, count($posts));
            }
        }

        return $results;
    }

    /**
     * Remove post from index
     */
    public function removeFromIndex(int $postId): bool
    {
        try {
            PostEmbedding::where('post_id', $postId)->delete();
            Cache::tags(['search'])->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if filters pass
     */
    private function passesFilters(Post $post, array $filters): bool
    {
        if (empty($filters)) {
            return true;
        }

        foreach ($filters as $field => $value) {
            if ($value === null) {
                continue;
            }

            $postValue = $post->{$field} ?? null;

            if (is_array($value)) {
                // Range filter
                if (isset($value['min']) && $postValue < $value['min']) {
                    return false;
                }
                if (isset($value['max']) && $postValue > $value['max']) {
                    return false;
                }
            } else {
                // Exact match
                if ($postValue != $value) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, array $filters): void
    {
        foreach ($filters as $field => $value) {
            if ($value === null) {
                continue;
            }

            if (is_array($value)) {
                if (isset($value['min'])) {
                    $query->where($field, '>=', $value['min']);
                }
                if (isset($value['max'])) {
                    $query->where($field, '<=', $value['max']);
                }
            } else {
                $query->where($field, $value);
            }
        }
    }

    /**
     * Get search suggestions
     */
    public function getSuggestions(string $query, int $limit = 5): array
    {
        // Simple prefix-based suggestions
        $suggestions = Post::query()
            ->where('title', 'LIKE', "{$query}%")
            ->orWhere('title', 'LIKE', "%{$query}%")
            ->select('title')
            ->distinct()
            ->limit($limit)
            ->pluck('title')
            ->toArray();

        return $suggestions;
    }
}
