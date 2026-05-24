@extends('layouts.dashboard')

@section('title', 'Thêm Facebook Group')

@section('breadcrumb')
    <a href="{{ route('dashboard.facebook-groups.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">Facebook Groups</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-700 dark:text-gray-300">Thêm mới</span>
@endsection

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Thêm Facebook Group</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Thêm Group mới để scrape</p>
</div>

<div class="max-w-2xl">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <form action="{{ route('dashboard.facebook-groups.store') }}" method="POST">
            @csrf
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tên Group <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 @error('name') ring-2 ring-red-500 @enderror"
                           placeholder="VD: Nhóm Bất động sản TP.HCM">
                    @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">URL Group <span class="text-red-500">*</span></label>
                    <input type="url" name="url" value="{{ old('url') }}" required
                           class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 @error('url') ring-2 ring-red-500 @enderror"
                           placeholder="https://www.facebook.com/groups/...">
                    @error('url')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Số bài scrape</label>
                        <input type="number" name="scrape_limit" value="{{ old('scrape_limit', 20) }}"
                               class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500"
                               placeholder="20">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Số bài viết tối đa mỗi lần scrape</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Ưu tiên</label>
                        <input type="number" name="priority" value="{{ old('priority', 1) }}"
                               class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500"
                               placeholder="1">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Số cao hơn = ưu tiên trước</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="enabled" id="enabled" value="1" {{ old('enabled', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-teal-600 bg-gray-100 dark:bg-gray-700 border-0 rounded focus:ring-2 focus:ring-teal-500">
                    <label for="enabled" class="text-sm font-medium text-gray-700 dark:text-gray-300">Bật auto-scrape</label>
                </div>
                
                <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Thêm Group
                    </button>
                    <a href="{{ route('dashboard.facebook-groups.index') }}" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                        Hủy
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
