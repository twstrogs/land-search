<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Repositories\PostRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function __construct(
        protected PostRepository $postRepository
    ) {}

    public function index(Request $request): View
    {
        $posts = $this->postRepository->getAllPaginated(20);
        
        return view('dashboard.posts.index', [
            'posts' => $posts,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Post::class);

        return view('dashboard.posts.create');
    }

    public function show(int $id): View
    {
        $post = $this->postRepository->findById($id);

        if (! $post) {
            abort(404);
        }

        Gate::authorize('view', $post);

        // Load contacts and features for display
        $post->load(['contacts', 'features']);

        return view('dashboard.posts.show', [
            'post' => $post,
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Post::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_text' => 'nullable|string|max:100',
            'price_value' => 'nullable|numeric|min:0',
            'area_text' => 'nullable|string|max:100',
            'area_value' => 'nullable|numeric|min:0',
            'address_text' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'property_type' => 'nullable|string|max:50',
        ]);

        $this->postRepository->create(array_merge($validated, [
            'user_id' => auth()->id(),
        ]));

        return redirect()
            ->route('dashboard.posts.index')
            ->with('success', 'Bài đăng đã được tạo thành công.');
    }

    public function edit(int $id): View
    {
        $post = $this->postRepository->findById($id);
        
        if (!$post) {
            abort(404);
        }

        Gate::authorize('update', $post);

        return view('dashboard.posts.edit', [
            'post' => $post,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $post = $this->postRepository->findById($id);
        
        if (!$post) {
            abort(404);
        }

        Gate::authorize('update', $post);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_text' => 'nullable|string|max:100',
            'price_value' => 'nullable|numeric|min:0',
            'area_text' => 'nullable|string|max:100',
            'area_value' => 'nullable|numeric|min:0',
            'address_text' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'property_type' => 'nullable|string|max:50',
        ]);

        $this->postRepository->update($post, $validated);

        return redirect()
            ->route('dashboard.posts.index')
            ->with('success', 'Bài đăng đã được cập nhật.');
    }

    public function destroy(int $id)
    {
        $post = $this->postRepository->findById($id);
        
        if (!$post) {
            abort(404);
        }

        Gate::authorize('delete', $post);

        $this->postRepository->delete($post);

        return redirect()
            ->route('dashboard.posts.index')
            ->with('success', 'Bài đăng đã được xóa.');
    }
}
