@extends('layouts.dashboard')

@section('title', 'Scrape')

@section('breadcrumb')
    <span class="text-gray-700 dark:text-gray-300">Scrape</span>
@endsection

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Scrape Facebook</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Thu thập bài viết từ Facebook Groups</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('dashboard.facebook-groups.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
            <span class="material-symbols-outlined text-xl">groups</span>
            Quản lý Groups
        </a>
        <form action="{{ route('dashboard.scrape.startAll') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition-colors">
                <span class="material-symbols-outlined text-xl">play_arrow</span>
                Scrape All
            </button>
        </form>
    </div>
</div>

<!-- Status Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-gray-600 dark:text-gray-400">groups</span>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $groups->count() }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Tổng Groups</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-green-600 dark:text-green-400">check_circle</span>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $groups->where('status', 'idle')->count() }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Sẵn sàng</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-yellow-600 dark:text-yellow-400">sync</span>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $groups->where('status', 'running')->count() }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Đang chạy</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-teal-600 dark:text-teal-400">task_alt</span>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $groups->sum('posts_scraped') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Đã scrape</p>
            </div>
        </div>
    </div>
</div>

<!-- Groups List -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Danh sách Groups</h3>
    </div>
    <div class="divide-y divide-gray-200 dark:divide-gray-700">
        @forelse($groups ?? [] as $group)
        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                        <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">group</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $group->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[300px]">{{ $group->url }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-sm text-gray-900 dark:text-white">{{ $group->posts_scraped ?? 0 }} bài</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Đã scrape</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($group->status === 'idle')
                            @if($group->enabled)
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Sẵn sàng</span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">Tắt</span>
                            @endif
                        @elseif($group->status === 'running')
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full animate-pulse"></span>
                                Đang chạy
                            </span>
                        @elseif($group->status === 'completed')
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400">Xong</span>
                        @elseif($group->status === 'failed')
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Lỗi</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if($group->enabled && $group->status !== 'running')
                            <form action="{{ route('dashboard.scrape.start-group', $group->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-2 rounded-lg bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 hover:bg-teal-200 dark:hover:bg-teal-900/50 transition-colors" title="Scrape group này">
                                    <span class="material-symbols-outlined">play_arrow</span>
                                </button>
                            </form>
                        @endif
                        @if($group->status === 'running')
                            <form action="{{ route('dashboard.scrape.stop-group', $group->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-2 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors" title="Dừng">
                                    <span class="material-symbols-outlined">stop</span>
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('dashboard.facebook-groups.edit', $group->id) }}" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-colors" title="Sửa">
                            <span class="material-symbols-outlined">edit</span>
                        </a>
                    </div>
                </div>
            </div>
            @if($group->last_error)
            <div class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-xs text-red-600 dark:text-red-400">{{ $group->last_error }}</p>
            </div>
            @endif
        </div>
        @empty
        <div class="p-12 text-center">
            <span class="material-symbols-outlined text-5xl text-gray-300 dark:text-gray-600">groups</span>
            <p class="text-gray-500 dark:text-gray-400 mt-3">Chưa có Facebook Group nào</p>
            <a href="{{ route('dashboard.facebook-groups.create') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition-colors">
                <span class="material-symbols-outlined">add</span>
                Thêm Group
            </a>
        </div>
        @endforelse
    </div>
</div>

<!-- Recent Logs -->
<div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Nhật ký gần đây</h3>
    </div>
    <div class="max-h-64 overflow-y-auto divide-y divide-gray-200 dark:divide-gray-700">
        @forelse($logs ?? [] as $log)
        <div class="px-6 py-3 flex items-start gap-3">
            @if($log->level === 'success')
                <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
            @elseif($log->level === 'error')
                <span class="material-symbols-outlined text-red-500 text-lg">error</span>
            @elseif($log->level === 'warning')
                <span class="material-symbols-outlined text-yellow-500 text-lg">warning</span>
            @else
                <span class="material-symbols-outlined text-blue-500 text-lg">info</span>
            @endif
            <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $log->message }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
            </div>
        </div>
        @empty
        <div class="p-6 text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">Chưa có nhật ký nào</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
