<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Repositories\PostRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchController extends Controller
{
    public function __construct(
        protected PostRepository $postRepository
    ) {}

    public function index(Request $request): View
    {
        $keyword = $request->get('keyword', '');
        
        // Build query with all filters
        $query = Post::query();
        
        // Keyword search
        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%")
                  ->orWhere('address_text', 'like', "%{$keyword}%")
                  ->orWhere('ward', 'like', "%{$keyword}%")
                  ->orWhere('district', 'like', "%{$keyword}%")
                  ->orWhere('city', 'like', "%{$keyword}%");
            });
        }
        
        // Property type filter
        if ($request->get('property_type')) {
            $query->where('property_type', $request->get('property_type'));
        }
        
        // Price range filter
        if ($request->get('price_min')) {
            $query->where('price_value', '>=', $request->float('price_min') * 1000000);
        }
        if ($request->get('price_max')) {
            $query->where('price_value', '<=', $request->float('price_max') * 1000000);
        }
        
        // Area range filter
        if ($request->get('area_min')) {
            $query->where('area_value', '>=', $request->float('area_min'));
        }
        if ($request->get('area_max')) {
            $query->where('area_value', '<=', $request->float('area_max'));
        }
        
        // Direction filter
        if ($request->get('direction')) {
            $query->where('direction', $request->get('direction'));
        }
        
        // Legal filter
        if ($request->get('legal')) {
            $query->where('legal_status', $request->get('legal'));
        }
        
        // Amenities filters
        if ($request->get('mat_tien')) {
            $query->where('frontage_count', '>', 0);
        }
        
        // Sort
        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price_value', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price_value', 'desc');
                break;
            case 'area_desc':
                $query->orderBy('area_value', 'desc');
                break;
            case 'views':
                $query->orderBy('views', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
        
        // Paginate with custom Tailwind view
        $posts = $query->paginate(20)->withQueryString();
        
        // Generate session token
        $sessionToken = $this->generateSearchSession($request, $posts);
        
        // Store last search keyword
        if ($keyword) {
            $request->session()->put('last_search_keyword', $keyword);
        }
        
        $propertyTypes = [
            'Căn hộ chung cư',
            'Nhà phố/Nhà riêng',
            'Biệt thự (Villa)',
            'Đất nền/Đất thổ cư',
        ];

        return view('public.search', [
            'posts' => $posts,
            'keyword' => $keyword,
            'sessionToken' => $sessionToken,
            'propertyTypes' => $propertyTypes,
        ]);
    }

    public function viewPost(Request $request, string $token): View
    {
        // Decode token to get post ID
        $postId = (int) base64_decode($token);
        
        $post = $this->postRepository->findById($postId);

        if (!$post) {
            abort(404, 'Post not found.');
        }
        
        // Verify post is in current search results (if session exists)
        $sessionKey = 'search_session_' . $request->session()->getId();
        $validToken = Cache::get("{$sessionKey}_token");
        $postIds = Cache::get("{$sessionKey}_post_ids", []);

        if ($validToken && hash_equals($validToken, $token)) {
            // Token is valid for this session - no additional check needed
        } elseif (!in_array($postId, $postIds)) {
            // Token invalid or post not in recent search - still allow view but don't track as search result
        }

        // Increment views
        $post->increment('views');

        return view('public.post', [
            'post' => $post,
        ]);
    }

    protected function generateSearchSession(Request $request, $posts): string
    {
        $sessionKey = 'search_session_' . $request->session()->getId();
        $token = bin2hex(random_bytes(16));

        $postIds = $posts->pluck('id')->toArray();

        Cache::put("{$sessionKey}_token", $token, now()->addMinutes(30));
        Cache::put("{$sessionKey}_post_ids", $postIds, now()->addMinutes(30));
        Cache::put("{$sessionKey}_expires", now()->addMinutes(30)->timestamp, now()->addMinutes(30));

        return $token;
    }
}
