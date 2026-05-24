<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Search\SemanticSearchService;
use App\Search\EmbeddingService;
use App\Models\Post;
use App\Models\PostEmbedding;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    private SemanticSearchService $searchService;
    private EmbeddingService $embeddingService;

    public function __construct()
    {
        $this->searchService = new SemanticSearchService();
        $this->embeddingService = new EmbeddingService();
    }

    /**
     * Semantic search
     */
    public function semantic(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:500',
            'property_type' => 'string|nullable',
            'district' => 'string|nullable',
            'price_min' => 'numeric|nullable',
            'price_max' => 'numeric|nullable',
            'area_min' => 'numeric|nullable',
            'area_max' => 'numeric|nullable',
            'limit' => 'integer|min:1|max:50',
            'semantic_weight' => 'numeric|min:0|max:1',
        ]);

        $filters = [];

        if ($request->has('property_type')) {
            $filters['property_type'] = $request->input('property_type');
        }

        if ($request->has('district')) {
            $filters['district'] = $request->input('district');
        }

        if ($request->has('price_min') || $request->has('price_max')) {
            $filters['price_value'] = [
                'min' => $request->input('price_min'),
                'max' => $request->input('price_max'),
            ];
        }

        if ($request->has('area_min') || $request->has('area_max')) {
            $filters['area_value'] = [
                'min' => $request->input('area_min'),
                'max' => $request->input('area_max'),
            ];
        }

        try {
            $results = $this->searchService->search(
                $request->input('q'),
                $filters,
                $request->input('limit', 20),
                $request->input('semantic_weight', 0.6)
            );

            // Format results
            $formatted = collect($results)->map(function ($result) {
                return [
                    'post_id' => $result['post_id'],
                    'similarity' => round($result['combined_score'] ?? 0, 4),
                    'semantic_score' => round($result['semantic_score'] ?? 0, 4),
                    'keyword_score' => round($result['keyword_score'] ?? 0, 4),
                    'post' => $result['post'] ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'query' => $request->input('q'),
                'total' => count($formatted),
                'data' => $formatted,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Index posts for semantic search
     */
    public function indexPost(Request $request): JsonResponse
    {
        $request->validate([
            'post_id' => 'required|integer',
        ]);

        $post = Post::find($request->input('post_id'));

        if (!$post) {
            return response()->json([
                'success' => false,
                'error' => 'Post not found',
            ], 404);
        }

        try {
            $success = $this->searchService->indexPost($post);

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Post indexed successfully' : 'Failed to index post',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Index multiple posts (batch)
     */
    public function indexBatch(Request $request): JsonResponse
    {
        $request->validate([
            'post_ids' => 'array',
            'post_ids.*' => 'integer',
            'all' => 'boolean',
        ]);

        try {
            $postIds = $request->input('post_ids', []);

            if ($request->input('all') && empty($postIds)) {
                $postIds = Post::pluck('id')->toArray();
            }

            $results = $this->searchService->indexBatch($postIds);

            return response()->json([
                'success' => true,
                'indexed' => $results['success'],
                'failed' => $results['failed'],
                'errors' => $results['errors'] ?? [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get search suggestions
     */
    public function suggestions(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
            'limit' => 'integer|min:1|max:10',
        ]);

        $suggestions = $this->searchService->getSuggestions(
            $request->input('q'),
            $request->input('limit', 5)
        );

        return response()->json([
            'success' => true,
            'data' => $suggestions,
        ]);
    }

    /**
     * Get embedding info
     */
    public function embeddingInfo(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->embeddingService->getProviderInfo(),
        ]);
    }

    /**
     * Get index statistics
     */
    public function indexStats(): JsonResponse
    {
        try {
            $totalPosts = Post::count();
            $indexedPosts = PostEmbedding::count();
            $staleEmbeddings = PostEmbedding::where('indexed_at', '<', now()->subDays(30))->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_posts' => $totalPosts,
                    'indexed_posts' => $indexedPosts,
                    'index_percentage' => $totalPosts > 0 ? round($indexedPosts / $totalPosts * 100, 2) : 0,
                    'stale_embeddings' => $staleEmbeddings,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
