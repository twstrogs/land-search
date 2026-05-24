@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('breadcrumb')
    <span class="text-gray-700 dark:text-gray-300">Dashboard</span>
@endsection

@section('content')
<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

    <!-- Total Posts -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tổng tin đăng</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_posts'] ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 bg-teal-100 dark:bg-teal-900/30 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-teal-600 dark:text-teal-400 text-2xl">article</span>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-teal-600 dark:text-teal-400 font-medium">+{{ number_format($stats['posts_today'] ?? 0) }}</span>
            <span class="text-gray-500 dark:text-gray-400 ml-1">hôm nay</span>
            <span class="text-gray-300 dark:text-gray-600 mx-2">•</span>
            <span class="text-gray-500 dark:text-gray-400">{{ number_format($stats['posts_this_week'] ?? 0) }} tuần này</span>
        </div>
    </div>

    <!-- Facebook Groups -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Facebook Groups</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_groups'] ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-2xl">groups</span>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
            <span class="text-green-600 dark:text-green-400 font-medium">{{ $stats['enabled_groups'] ?? 0 }}</span>
            <span class="text-gray-500 dark:text-gray-400 ml-1">đang hoạt động</span>
        </div>
    </div>

    <!-- Import Batches -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Lần Import</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_batches'] ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-purple-600 dark:text-purple-400 text-2xl">upload_file</span>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="material-symbols-outlined text-green-500 text-sm">check_circle</span>
            <span class="text-green-600 dark:text-green-400 font-medium ml-1">{{ $stats['completed_batches'] ?? 0 }}</span>
            <span class="text-gray-500 dark:text-gray-400 ml-1">hoàn thành</span>
            @if(($stats['processing_batches'] ?? 0) > 0)
            <span class="text-gray-300 dark:text-gray-600 mx-2">•</span>
            <span class="text-blue-600 dark:text-blue-400 font-medium">{{ $stats['processing_batches'] ?? 0 }}</span>
            <span class="text-gray-500 dark:text-gray-400 ml-1">đang xử lý</span>
            @endif
        </div>
    </div>

    <!-- Processed Records -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Đã xử lý</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_processed'] ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-orange-600 dark:text-orange-400 text-2xl">task_alt</span>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm gap-4">
            <span class="text-red-500 flex items-center">
                <span class="material-symbols-outlined text-sm">error</span>
                <span class="font-medium ml-1">{{ number_format($stats['total_failed'] ?? 0) }}</span>
            </span>
            <span class="text-yellow-500 flex items-center">
                <span class="material-symbols-outlined text-sm">skip_next</span>
                <span class="font-medium ml-1">{{ number_format($stats['total_skipped'] ?? 0) }}</span>
            </span>
            <span class="text-blue-500 flex items-center">
                <span class="material-symbols-outlined text-sm">content_copy</span>
                <span class="font-medium ml-1">{{ number_format($stats['total_duplicates'] ?? 0) }}</span>
            </span>
        </div>
    </div>
</div>

<!-- Quick Actions & Recent Activity -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Quick Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Thao tác nhanh</h3>
        </div>
        <div class="p-6 space-y-3">
            <a href="{{ route('dashboard.import.create') }}" class="flex items-center gap-3 p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <div class="w-10 h-10 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-teal-600 dark:text-teal-400">upload_file</span>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Import JSON</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tải lên file dữ liệu</p>
                </div>
                <span class="material-symbols-outlined text-gray-400">arrow_forward</span>
            </a>
            
            <a href="{{ route('dashboard.scrape.index') }}" class="flex items-center gap-3 p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">rss_feed</span>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Scrape Facebook</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Thu thập bài viết từ Groups</p>
                </div>
                <span class="material-symbols-outlined text-gray-400">arrow_forward</span>
            </a>
            
            <a href="{{ route('dashboard.posts.create') }}" class="flex items-center gap-3 p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-purple-600 dark:text-purple-400">add_circle</span>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Thêm tin đăng</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tạo tin rao bán/cho thuê</p>
                </div>
                <span class="material-symbols-outlined text-gray-400">arrow_forward</span>
            </a>
            
            <a href="{{ route('dashboard.facebook-groups.create') }}" class="flex items-center gap-3 p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-orange-600 dark:text-orange-400">group_add</span>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Thêm Group</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Thêm Facebook Group mới</p>
                </div>
                <span class="material-symbols-outlined text-gray-400">arrow_forward</span>
            </a>
        </div>
    </div>
    
    <!-- Recent Posts -->
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tin đăng gần đây</h3>
            <a href="{{ route('dashboard.posts.index') }}" class="text-sm text-teal-600 dark:text-teal-400 hover:underline">Xem tất cả</a>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($recentPosts ?? [] as $post)
            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <div class="flex items-start gap-4">
                    @if($post->image_url)
                    <img src="{{ $post->image_url }}" alt="" class="w-16 h-16 rounded-lg object-cover flex-shrink-0">
                    @else
                    <div class="w-16 h-16 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-gray-400">image</span>
                    </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $post->title }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $post->area_text ?? 'N/A' }} · {{ $post->price_text ?? 'Thương lượng' }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            {{ $post->city ?? '' }}{{ $post->district ? ', ' . $post->district : '' }}
                        </p>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                            @if($post->property_type === 'Nhà phố/Nhà riêng') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                            @elseif($post->property_type === 'Căn hộ chung cư') bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400
                            @elseif($post->property_type === 'Đất nền/Đất thổ cư') bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400
                            @else bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300
                            @endif">
                            {{ $post->property_type ?? 'Khác' }}
                        </span>
                        <span class="text-xs text-gray-400">{{ $post->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center">
                <span class="material-symbols-outlined text-4xl text-gray-300 dark:text-gray-600">inbox</span>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Chưa có tin đăng nào</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
