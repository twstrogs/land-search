<?php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class PostRepository
{
    protected const CACHE_PREFIX = 'posts:';
    protected const CACHE_TTL = 3600;

    public function getAllPaginated(int $perPage = 20): LengthAwarePaginator
    {
        return Post::with(['contacts', 'features'])
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    public function search(
        ?string $keyword = null,
        ?float $priceMin = null,
        ?float $priceMax = null,
        ?float $areaMin = null,
        ?float $areaMax = null,
        ?string $propertyType = null,
        ?string $city = null,
        ?string $district = null,
        string $sortBy = 'latest',
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = Post::with(['contacts', 'features']);

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'LIKE', "%{$keyword}%")
                  ->orWhere('description', 'LIKE', "%{$keyword}%")
                  ->orWhere('address_text', 'LIKE', "%{$keyword}%")
                  ->orWhere('district', 'LIKE', "%{$keyword}%")
                  ->orWhere('city', 'LIKE', "%{$keyword}%");
            });
        }

        if ($priceMin !== null && $priceMin > 0) {
            $query->where('price_value', '>=', $priceMin);
        }

        if ($priceMax !== null && $priceMax > 0) {
            $query->where('price_value', '<=', $priceMax);
        }

        if ($areaMin !== null && $areaMin > 0) {
            $query->where('area_value', '>=', $areaMin);
        }

        if ($areaMax !== null && $areaMax > 0) {
            $query->where('area_value', '<=', $areaMax);
        }

        if ($propertyType) {
            $query->where('property_type', $propertyType);
        }

        if ($city) {
            $query->where('city', 'LIKE', "%{$city}%");
        }

        if ($district) {
            $query->where('district', 'LIKE', "%{$district}%");
        }

        $query->orderBy(match ($sortBy) {
            'price_asc' => 'price_value',
            'price_desc' => 'price_value',
            'area_asc' => 'area_value',
            'area_desc' => 'area_value',
            default => 'published_at',
        }, match ($sortBy) {
            'price_asc', 'area_asc' => 'asc',
            default => 'desc',
        });

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Post
    {
        return Cache::remember(self::CACHE_PREFIX . "id:{$id}", self::CACHE_TTL, function () use ($id) {
            return Post::with(['contacts', 'features', 'aiExtractions'])->find($id);
        });
    }

    public function findByHash(string $hash): ?Post
    {
        return Post::where('hash', $hash)->first();
    }

    public function findByRawContentHash(string $hash): ?Post
    {
        return Post::where('raw_content_hash', $hash)->first();
    }

    public function create(array $data): Post
    {
        $post = Post::create($data);
        $this->clearCache($post->id);
        return $post;
    }

    public function update(Post $post, array $data): Post
    {
        $post->update($data);
        $this->clearCache($post->id);
        return $post;
    }

    public function delete(Post $post): void
    {
        $postId = $post->id;
        $post->delete();
        $this->clearCache($postId);
    }

    public function getStats(): array
    {
        return Cache::remember('posts:stats', self::CACHE_TTL, function () {
            return [
                'total' => Post::count(),
                'today' => Post::whereDate('created_at', today())->count(),
                'this_week' => Post::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'this_month' => Post::whereMonth('created_at', now()->month)->count(),
                'by_type' => Post::selectRaw('property_type, count(*) as count')
                    ->groupBy('property_type')
                    ->pluck('count', 'property_type')
                    ->toArray(),
            ];
        });
    }

    public function getRecentPosts(int $limit = 10): Collection
    {
        return Post::with(['contacts', 'features'])
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    protected function clearCache(int $postId): void
    {
        Cache::forget(self::CACHE_PREFIX . "id:{$postId}");
        Cache::forget('posts:stats');
    }
}
