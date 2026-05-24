<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        // Get featured posts (paginated)
        $posts = Post::query()
            ->where(function($query) {
                $query->where('city', 'like', '%Thái Nguyên%')
                      ->orWhereNull('city')
                      ->orWhere('city', '=', 'Thái Nguyên');
            })
            ->orderBy('created_at', 'desc')
            ->simplePaginate(6);
        
        // Stats for homepage
        $stats = [
            'total_posts' => Post::count(),
            'total_wards' => Post::whereNotNull('ward')->distinct('ward')->count('ward'),
        ];
        
        // Generate session token for viewing posts
        $sessionToken = $request->session()->get('session_token', bin2hex(random_bytes(16)));
        $request->session()->put('session_token', $sessionToken);
        
        return view('welcome', [
            'posts' => $posts,
            'stats' => $stats,
            'sessionToken' => $sessionToken,
        ]);
    }
}
