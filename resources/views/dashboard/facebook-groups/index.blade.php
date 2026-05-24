@extends('layouts.dashboard')

@section('title', 'Facebook Groups')

@section('breadcrumb')
    <span class="text-gray-700 dark:text-gray-300">Facebook Groups</span>
@endsection

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Facebook Groups</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Quản lý danh sách Groups để scrape</p>
    </div>
    <a href="{{ route('dashboard.facebook-groups.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition-colors">
        <span class="material-symbols-outlined text-xl">add</span>
        Thêm Group
    </a>
</div>

<!-- Groups Table -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Group</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">URL</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Trạng thái</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Đã scrape</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Lần scrape cuối</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($groups ?? [] as $group)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">group</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $group->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Ưu tiên: {{ $group->priority }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ $group->url }}" target="_blank" class="text-sm text-teal-600 dark:text-teal-400 hover:underline truncate max-w-[200px] block">
                            {{ Str::limit($group->url, 40) }}
                        </a>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            @if($group->enabled)
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Bật</span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">Tắt</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900 dark:text-white">{{ number_format($group->posts_scraped ?? 0) }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if($group->last_scrape_at)
                                {{ $group->last_scrape_at->diffForHumans() }}
                            @else
                                -
                            @endif
                        </p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('dashboard.facebook-groups.edit', $group->id) }}" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400" title="Sửa">
                                <span class="material-symbols-outlined">edit</span>
                            </a>
                            <form action="{{ route('dashboard.facebook-groups.destroy', $group->id) }}" method="POST" class="inline" onsubmit="return confirm('Xóa group này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 text-red-500" title="Xóa">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <span class="material-symbols-outlined text-5xl text-gray-300 dark:text-gray-600">groups</span>
                        <p class="text-gray-500 dark:text-gray-400 mt-3">Chưa có Facebook Group nào</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if(isset($groups) && $groups->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        {{ $groups->links() }}
    </div>
    @endif
</div>
@endsection
