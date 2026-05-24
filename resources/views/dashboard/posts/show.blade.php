@extends('layouts.dashboard')

@section('title', 'Chi tiết tin đăng')

@section('breadcrumb')
    <a href="{{ route('dashboard.posts.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">Tin đăng</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-700 dark:text-gray-300">Chi tiết</span>
@endsection

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Chi tiết tin đăng</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $post->published_at?->format('d/m/Y H:i') ?? $post->created_at->format('d/m/Y H:i') }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('dashboard.posts.edit', $post->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg">
            <span class="material-symbols-outlined">edit</span>
            Sửa
        </a>
        <a href="{{ route('dashboard.posts.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-sm font-medium rounded-lg">
            <span class="material-symbols-outlined">arrow_back</span>
            Quay lại
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">{{ $post->title ?: '(Không có tiêu đề)' }}</h2>
            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $post->description ?: $post->raw_content ?: 'Không có mô tả' }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ảnh</h3>
            @if(!empty($post->images))
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($post->images as $image)
                        <a href="{{ $image }}" target="_blank" class="block">
                            <img src="{{ $image }}" class="w-full h-36 object-cover rounded-lg border border-gray-200 dark:border-gray-700" alt="Post image">
                        </a>
                    @endforeach
                </div>
            @elseif($post->image_url)
                <a href="{{ $post->image_url }}" target="_blank">
                    <img src="{{ $post->image_url }}" class="w-full max-w-lg object-cover rounded-lg border border-gray-200 dark:border-gray-700" alt="Post image">
                </a>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">Không có ảnh.</p>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Thông tin chính</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Giá</dt><dd class="text-gray-900 dark:text-white font-medium">{{ $post->price_text ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Diện tích</dt><dd class="text-gray-900 dark:text-white">{{ $post->area_text ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Mặt tiền</dt><dd class="text-gray-900 dark:text-white">{{ $post->frontage_count ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Chiều sâu</dt><dd class="text-gray-900 dark:text-white">{{ $post->depth_text ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Loại BĐS</dt><dd class="text-gray-900 dark:text-white">{{ $post->property_type ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Phường/Xã</dt><dd class="text-gray-900 dark:text-white">{{ $post->ward ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Quận/Huyện</dt><dd class="text-gray-900 dark:text-white">{{ $post->district ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Tỉnh/TP</dt><dd class="text-gray-900 dark:text-white">{{ $post->city ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Địa chỉ</dt><dd class="text-gray-900 dark:text-white">{{ $post->address_text ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Độ tin cậy</dt><dd class="text-gray-900 dark:text-white">{{ $post->confidence !== null ? $post->confidence : '-' }}</dd></div>
            </dl>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Liên hệ</h3>
            @if($post->contacts->isNotEmpty())
                <div class="space-y-2">
                    @foreach($post->contacts as $contact)
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-gray-500">phone</span>
                            <a href="tel:{{ $contact->phone }}" class="text-teal-600 dark:text-teal-400 hover:underline font-medium">
                                {{ $contact->phone }}
                            </a>
                            @if($contact->label)
                                <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                    {{ $contact->label }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">Không có số điện thoại.</p>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Đặc điểm</h3>
            @if($post->features->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach($post->features as $feature)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-teal-500 text-teal-800 dark:!bg-teal-500 dark:!text-indigo-300">
                                {{ $feature->name }}
                            </span>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">Không có đặc điểm nổi bật.</p>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Nguồn</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-gray-500">ID</dt><dd class="text-gray-900 dark:text-white">#{{ $post->id }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Tác giả</dt><dd class="text-gray-900 dark:text-white">{{ $post->author_name ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Nhóm nguồn</dt><dd class="text-gray-900 dark:text-white">{{ $post->source_group ?: '-' }}</dd></div>
            </dl>
            @if($post->facebook_url)
                <a href="{{ $post->facebook_url }}" target="_blank" class="inline-flex items-center gap-2 mt-4 text-teal-600 dark:text-teal-400 hover:underline">
                    <span class="material-symbols-outlined text-base">open_in_new</span>
                    Mở bài gốc Facebook
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
