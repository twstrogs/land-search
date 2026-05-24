<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ImportBatch;
use App\Models\SourceRecord;
use App\Models\FacebookGroup;
use App\Repositories\PostRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardHomeController extends Controller
{
    public function __construct(
        protected PostRepository $postRepository
    ) {}

    public function index(Request $request): View
    {
        $stats = $this->getDashboardStats();
        $recentPosts = $this->postRepository->getRecentPosts(5);

        return view('dashboard.index', [
            'stats' => $stats,
            'recentPosts' => $recentPosts,
        ]);
    }

    protected function getDashboardStats(): array
    {
        return [
            'total_posts' => \App\Models\Post::count(),
            'total_groups' => FacebookGroup::count(),
            'enabled_groups' => FacebookGroup::where('enabled', true)->count(),
            'total_batches' => ImportBatch::count(),
            'completed_batches' => ImportBatch::where('status', 'completed')->count(),
            'processing_batches' => ImportBatch::where('status', 'processing')->count(),
            'total_processed' => SourceRecord::where('status', 'completed')->count(),
            'total_failed' => SourceRecord::where('status', 'failed')->count(),
            'total_skipped' => SourceRecord::where('status', 'skipped')->count(),
            'total_duplicates' => SourceRecord::where('status', 'duplicate')->count(),
            'posts_today' => \App\Models\Post::whereDate('created_at', today())->count(),
            'posts_this_week' => \App\Models\Post::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'posts_this_month' => \App\Models\Post::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];
    }
}
