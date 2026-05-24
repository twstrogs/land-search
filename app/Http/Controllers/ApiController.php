<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use App\Models\Post;
use App\Services\AI\AiServiceFactory;
use App\Services\NormalizeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class ApiController extends Controller
{
    /**
     * Search posts with filters
     *
     * GET /api/v1/posts
     */
    public function search(Request $request): JsonResponse
    {
        $query = Post::with(['contacts', 'features']);

        // Keyword search
        $keyword = $request->get('keyword', $request->get('q'));
        if ($keyword) {
            $query->search($keyword);
        }

        // Filters
        $query->filterByPrice($request->float('price_min'), $request->float('price_max'));
        $query->filterByArea($request->float('area_min'), $request->float('area_max'));
        $query->filterByPropertyType($request->get('property_type'));
        $query->filterByLocation($request->get('city'), $request->get('district'));

        // Sorting
        $sortBy = $request->get('sort', 'newest');
        switch ($sortBy) {
            case 'price_asc':
                $query->orderByRaw('CAST(price_value AS DECIMAL) ASC');
                break;
            case 'price_desc':
                $query->orderByRaw('CAST(price_value AS DECIMAL) DESC');
                break;
            case 'area_asc':
                $query->orderByRaw('CAST(area_value AS DECIMAL) ASC');
                break;
            case 'area_desc':
                $query->orderByRaw('CAST(area_value AS DECIMAL) DESC');
                break;
            default:
                $query->orderBy('published_at', 'desc');
        }

        $perPage = min((int) $request->get('per_page', 20), 100);
        $posts = $query->paginate($perPage);

        // Transform posts to include image URLs
        $transformedPosts = collect($posts->items())->map(function ($post) {
            return $this->transformPost($post);
        });

        return response()->json([
            'success' => true,
            'data' => $transformedPosts,
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'from' => $posts->firstItem(),
                'to' => $posts->lastItem(),
            ],
            'links' => [
                'first' => $posts->url(1),
                'last' => $posts->url($posts->lastPage()),
                'prev' => $posts->previousPageUrl(),
                'next' => $posts->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Get single post by ID
     *
     * GET /api/v1/posts/{id}
     */
    public function show(int $id): JsonResponse
    {
        $post = Post::with(['contacts', 'features'])->find($id);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->transformPost($post),
        ]);
    }

    /**
     * Get filter metadata
     *
     * GET /api/v1/metadata
     */
    public function metadata(): JsonResponse
    {
        $posts = Post::query();

        // Get unique cities
        $cities = Post::whereNotNull('city')
            ->distinct()
            ->pluck('city')
            ->sort()
            ->values();

        // Get unique districts grouped by city
        $districtsByCity = Post::whereNotNull('city')
            ->whereNotNull('district')
            ->select('city', 'district')
            ->distinct()
            ->get()
            ->groupBy('city')
            ->map(fn($group) => $group->pluck('district')->sort()->values());

        return response()->json([
            'success' => true,
            'data' => [
                'property_types' => [
                    'Căn hộ chung cư',
                    'Nhà phố/Nhà riêng',
                    'Biệt thự (Villa)',
                    'Đất nền/Đất thổ cư',
                ],
                'features' => Feature::pluck('name')->sort()->values(),
                'cities' => $cities,
                'districts' => $districtsByCity,
            ],
        ]);
    }

    /**
     * AI extraction endpoint for arbitrary content
     *
     * POST /api/v1/extract
     */
    public function extract(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:50000',
            'provider' => 'nullable|string|in:ollama,gemini,openai',
        ]);

        $content = $request->input('content');
        $provider = $request->input('provider');

        try {
            // Extract with AI
            $extracted = AiServiceFactory::extract($content, $provider);

            if (isset($extracted['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI extraction failed',
                    'error' => $extracted['error'],
                ], 500);
            }

            // Normalize
            $normalizeService = app(NormalizeService::class);
            $normalized = $normalizeService->normalize($extracted);

            return response()->json([
                'success' => true,
                'data' => [
                    'extracted' => $extracted,
                    'normalized' => $normalized,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'AI extraction failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Transform post model to API response format
     */
    protected function transformPost(Post $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'description' => $post->description,
            'raw_content' => $post->raw_content,
            'price_text' => $post->price_text,
            'price_value' => (float) $post->price_value,
            'area_text' => $post->area_text,
            'area_value' => (float) $post->area_value,
            'frontage_texts' => $post->frontage_texts ?? [],
            'frontage_count' => $post->frontage_count ? (float) $post->frontage_count : null,
            'depth_text' => $post->depth_text,
            'address_text' => $post->address_text,
            'ward' => $post->ward,
            'district' => $post->district,
            'city' => $post->city,
            'property_type' => $post->property_type,
            'image_url' => $post->image_url, // Already transformed via accessor
            'images' => $post->images, // Already transformed via accessor
            'author_id' => $post->author_id,
            'author_name' => $post->author_name,
            'facebook_url' => $post->facebook_url,
            'published_at' => $post->published_at?->toIso8601String(),
            'source_group' => $post->source_group,
            'confidence' => (float) $post->confidence,
            'features' => $post->features_list,
            'phones' => $post->phones,
            'created_at' => $post->created_at?->toIso8601String(),
            'updated_at' => $post->updated_at?->toIso8601String(),
        ];
    }
}
