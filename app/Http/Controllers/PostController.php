<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $query = Post::with(['contacts', 'features']);

        $query->search($request->get('q'));
        
        $query->filterByPrice(
            $request->float('price_min'),
            $request->float('price_max')
        );
        
        $query->filterByArea(
            $request->float('area_min'),
            $request->float('area_max')
        );
        
        $query->filterByPropertyType($request->get('property_type'));
        
        $query->filterByLocation(
            $request->get('city'),
            $request->get('district')
        );

        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price_value', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price_value', 'desc');
                break;
            case 'area_asc':
                $query->orderBy('area_value', 'asc');
                break;
            case 'area_desc':
                $query->orderBy('area_value', 'desc');
                break;
            default:
                $query->orderBy('published_at', 'desc');
        }

        $posts = $query->paginate(20)->withQueryString();

        $propertyTypes = [
            'Căn hộ chung cư',
            'Nhà phố/Nhà riêng',
            'Biệt thự (Villa)',
            'Đất nền/Đất thổ cư',
        ];

        $popularFeatures = Feature::withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit(10)
            ->pluck('name');

        return view('posts.index', compact('posts', 'propertyTypes', 'popularFeatures'));
    }

    public function show(int $id): View
    {
        $post = Post::with(['contacts', 'features', 'aiExtractions'])->findOrFail($id);

        return view('posts.show', compact('post'));
    }

    public function search(Request $request): View
    {
        return $this->index($request);
    }
}
