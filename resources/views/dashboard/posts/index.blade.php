@extends('layouts.dashboard')

@section('title', 'Tin đăng')

@section('breadcrumb')
    <span class="text-gray-700 dark:text-gray-300">Tin đăng</span>
@endsection

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tin đăng</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Quản lý tin rao bất động sản</p>
    </div>
    <a href="{{ route('dashboard.posts.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition-colors">
        <span class="material-symbols-outlined text-xl">add</span>
        Thêm tin đăng
    </a>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <form action="{{ route('dashboard.posts.index') }}" method="GET" class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm..." 
                   class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500">
        </div>
        <select name="property_type" class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm">
            <option value="">Loại BĐS</option>
            <option value="Nhà phố/Nhà riêng" {{ request('property_type') == 'Nhà phố/Nhà riêng' ? 'selected' : '' }}>Nhà phố</option>
            <option value="Căn hộ chung cư" {{ request('property_type') == 'Căn hộ chung cư' ? 'selected' : '' }}>Căn hộ</option>
            <option value="Đất nền/Đất thổ cư" {{ request('property_type') == 'Đất nền/Đất thổ cư' ? 'selected' : '' }}>Đất nền</option>
            <option value="Biệt thự (Villa)" {{ request('property_type') == 'Biệt thự (Villa)' ? 'selected' : '' }}>Biệt thự</option>
        </select>
        <select name="city" class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm">
            <option value="">Tất cả Tỉnh/TP</option>
        </select>
        <button type="submit" class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition-colors">
            Lọc
        </button>
    </form>
</div>

<!-- Posts Table -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tin đăng</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Loại</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Giá</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Diện tích</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Địa chỉ</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ngày đăng</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($posts ?? [] as $post)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @if($post->image_url)
                            <img src="{{ $post->image_url }}" alt="" class="w-12 h-12 rounded-lg object-cover">
                            @else
                            <div class="w-12 h-12 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                <span class="material-symbols-outlined text-gray-400">image</span>
                            </div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-[200px]">{{ $post->title }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[200px]">{{ Str::limit($post->description, 50) }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                            {{ $post->property_type ?? 'Khác' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $post->price_text ?? 'Thương lượng' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $post->area_text ?? '-' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-600 dark:text-gray-300 max-w-[150px] truncate">{{ $post->district ? $post->district . ', ' . $post->city : ($post->city ?? '-') }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $post->published_at?->format('d/m/Y') ?? $post->created_at->format('d/m/Y') }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('dashboard.posts.show', $post->id) }}" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-colors" title="Xem chi tiết">
                                <span class="material-symbols-outlined">visibility</span>
                            </a>
                            <a href="{{ route('dashboard.posts.edit', $post->id) }}" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-colors" title="Sửa">
                                <span class="material-symbols-outlined">edit</span>
                            </a>
                            <form action="{{ route('dashboard.posts.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 text-red-500 transition-colors" title="Xóa">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <span class="material-symbols-outlined text-5xl text-gray-300 dark:text-gray-600">inbox</span>
                        <p class="text-gray-500 dark:text-gray-400 mt-3">Chưa có tin đăng nào</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if(isset($posts) && $posts->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        {{ $posts->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
