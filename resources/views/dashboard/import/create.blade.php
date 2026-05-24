@extends('layouts.dashboard')

@section('title', 'Import mới')

@section('breadcrumb')
    <a href="{{ route('dashboard.import.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">Import</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-700 dark:text-gray-300">Tạo mới</span>
@endsection

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Import dữ liệu mới</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Upload file JSON chứa dữ liệu bất động sản</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Upload Form -->
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
            </div>
            @endif
            
            @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-sm text-red-700 dark:text-red-400">{{ session('error') }}</p>
            </div>
            @endif
            
            @if($errors->any())
            <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg">
                <ul class="text-sm text-red-700 dark:text-red-400 list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <form action="{{ route('dashboard.import.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tên lần import</label>
                        <input type="text" name="name" value="{{ old('name', 'Import ' . now()->format('Y-m-d H:i')) }}"
                               class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500"
                               placeholder="Nhập tên lần import">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">File JSON <span class="text-red-500">*</span></label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg hover:border-teal-500 transition-colors">
                            <div class="space-y-1 text-center">
                                <span class="material-symbols-outlined text-4xl text-gray-400">upload_file</span>
                                <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                    <label for="file" class="relative cursor-pointer rounded-md font-medium text-teal-600 hover:text-teal-500 focus-within:outline-none">
                                        <span>Chọn file</span>
                                        <input id="file" name="file" type="file" class="sr-only" accept=".json" required>
                                    </label>
                                    <p class="pl-1">hoặc kéo thả vào đây</p>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-500">JSON file, tối đa 50MB</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Định dạng JSON</h4>
                        <pre class="text-xs text-gray-600 dark:text-gray-400 overflow-x-auto"><code>{
  "raw_content": "Nội dung bài đăng...",
  "source_group": "Tên nhóm (tùy chọn)",
  "author_name": "Tên tác giả (tùy chọn)",
  "facebook_url": "URL bài viết (tùy chọn)",
  "image_url": "URL ảnh (tùy chọn)"
}</code></pre>
                    </div>
                    
                    <button type="submit" class="w-full px-4 py-3 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">upload</span>
                        Bắt đầu Import
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Info -->
    <div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Thông tin</h3>
            
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-teal-600">info</span>
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">AI Extraction</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Dữ liệu sẽ được xử lý bởi AI để trích xuất thông tin cấu trúc.</p>
                    </div>
                </div>
                
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-blue-600">image</span>
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Tải ảnh về</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Ảnh từ Facebook sẽ được tải về và lưu trữ cục bộ.</p>
                    </div>
                </div>
                
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-purple-600">key</span>
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Chống trùng lặp</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Bài viết trùng lặp sẽ được tự động bỏ qua.</p>
                    </div>
                </div>
                
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-orange-600">schedule</span>
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Xử lý nền</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Import được xử lý trong background, bạn có thể đóng trình duyệt.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
