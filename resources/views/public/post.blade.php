@extends('layouts.app')

@section('content')
<!-- Schema.org markup for SEO -->
@push('schema')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "RealEstateListing",
    "name": "{{ $post->title ?? 'Bất động sản tại Thái Nguyên' }}",
    "description": "{{ Str::limit(strip_tags($post->description ?? ''), 160) }}",
    "geo": {
        "@type": "GeoCoordinates",
        "latitude": "21.5900",
        "longitude": "105.8400"
    },
    "offers": {
        "@type": "Offer",
        "price": "{{ $post->price_value ?? '' }}",
        "priceCurrency": "VND"
    }
}
</script>
@endpush

<!-- Page Header -->
<section class="pt-20 pb-4">
    <div class="container mx-auto px-4 lg:px-6">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-teal-600 dark:hover:text-teal-400 transition-colors">Trang chủ</a>
            <span class="material-symbols-outlined text-lg">chevron_right</span>
            <a href="{{ route('search', ['keyword' => session('last_search_keyword', '')]) }}" class="hover:text-teal-600 dark:hover:text-teal-400 transition-colors">Tìm kiếm</a>
            <span class="material-symbols-outlined text-lg">chevron_right</span>
            <span class="text-slate-700 dark:text-slate-300 line-clamp-1">{{ $post->title ?? 'Chi tiết' }}</span>
        </nav>
    </div>
</section>

<!-- Main Content -->
<article class="pb-16">
    <div class="container mx-auto px-4 lg:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- =====================================================
                 MAIN CONTENT (2/3)
            ===================================================== -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- =====================================================
                     IMAGE GALLERY
                ===================================================== -->
                <section class="relative" aria-label="Hình ảnh bất động sản">
                    <!-- Main Image -->
                    <div id="mainImageContainer" class="relative aspect-[16/10] lg:aspect-[2/1] rounded-2xl lg:rounded-3xl overflow-hidden bg-slate-100 dark:bg-slate-800 cursor-pointer group" onclick="openGallery(0)">
                        @if($post->image_url)
                            <img id="mainImage" 
                                 src="{{ $post->image_url }}" 
                                 alt="{{ $post->title }}" 
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                 loading="eager">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <span class="material-symbols-outlined text-8xl text-slate-300 dark:text-slate-600">landscape</span>
                            </div>
                        @endif
                        
                        <!-- Overlay on hover -->
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity bg-white/90 dark:bg-slate-800/90 rounded-full px-4 py-2 flex items-center gap-2">
                                <span class="material-symbols-outlined">zoom_in</span>
                                <span class="text-sm font-medium">Xem chi tiết ảnh</span>
                            </div>
                        </div>
                        
                        <!-- Image counter -->
                        @if(isset($post->images) && count($post->images) > 1)
                        <div class="absolute bottom-4 right-4 px-3 py-1.5 bg-black/70 text-white text-sm font-medium rounded-lg flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-base">photo_library</span>
                            <span>1 / {{ count($post->images) }}</span>
                        </div>
                        @endif
                        
                        <!-- Expand button -->
                        <button onclick="event.stopPropagation(); openGallery(0)" class="absolute top-4 right-4 w-10 h-10 bg-white/90 dark:bg-slate-800/90 rounded-full flex items-center justify-center shadow-lg hover:bg-white dark:hover:bg-slate-800 transition-colors" aria-label="Phóng to">
                            <span class="material-symbols-outlined">fullscreen</span>
                        </button>
                    </div>
                    
                    <!-- Thumbnail Strip -->
                    @if(isset($post->images) && count($post->images) > 1)
                    <div class="flex gap-2 mt-3 overflow-x-auto scrollbar-hide pb-1">
                        @foreach($post->images as $index => $image)
                        <button onclick="changeMainImage({{ $index }})" 
                                class="thumbnail-btn flex-shrink-0 w-20 h-14 rounded-lg overflow-hidden border-2 border-transparent hover:border-teal-500 transition-all {{ $index === 0 ? 'border-teal-500' : '' }}"
                                data-index="{{ $index }}">
                            <img src="{{ $image }}" alt="Ảnh {{ $index + 1 }}" class="w-full h-full object-cover" loading="lazy">
                        </button>
                        @endforeach
                    </div>
                    @endif
                </section>
                
                <!-- =====================================================
                     TITLE & PRICE
                ===================================================== -->
                <header class="bg-white dark:bg-slate-800 rounded-2xl p-6 lg:p-8 shadow-lg border border-slate-200 dark:border-slate-700">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                        <div class="flex-1">
                            <!-- Badges -->
                            <div class="flex flex-wrap gap-2 mb-3">
                                @if($post->property_type)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 rounded-full text-xs font-semibold border border-teal-200 dark:border-teal-700">
                                    <span class="material-symbols-outlined text-xs">home</span>
                                    <span>{{ $post->property_type }}</span>
                                </span>
                                @endif
                                @if($post->is_owner ?? false)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-xs font-semibold border border-blue-200 dark:border-blue-700">
                                    <span class="material-symbols-outlined text-xs">verified</span>
                                    <span>Chính chủ</span>
                                </span>
                                @endif
                            </div>
                            
                            <!-- Title -->
                            <h1 class="text-xl lg:text-2xl font-bold text-slate-900 dark:text-white leading-tight mb-2">
                                {{ $post->title ?? 'Không có tiêu đề' }}
                            </h1>
                            
                            <!-- Address -->
                            <div class="flex items-start gap-2 text-slate-600 dark:text-slate-400">
                                <span class="material-symbols-outlined text-lg flex-shrink-0 mt-0.5">location_on</span>
                                <span>{{ $post->address_text ?? $post->ward . ', ' . $post->district . ', Thái Nguyên' }}</span>
                            </div>
                        </div>
                        
                        <!-- Price -->
                        <div class="text-left lg:text-right">
                            <p class="text-2xl lg:text-3xl font-bold text-teal-600 dark:text-teal-400">
                                {{ $post->price_formatted ?? 'Thỏa thuận' }}
                            </p>
                            @if($post->price_value)
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                {{ number_format($post->price_value) }} VNĐ
                            </p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
                        @if($post->area_value)
                        <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl">
                            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-lg text-emerald-600 dark:text-emerald-400">square_foot</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $post->area_formatted ?? $post->area_value . 'm²' }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Diện tích</p>
                            </div>
                        </div>
                        @endif
                        
                        @if($post->frontage_count > 0)
                        <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-lg text-blue-600 dark:text-blue-400">straighten</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $post->frontage_count }} mặt</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Mặt tiền</p>
                            </div>
                        </div>
                        @endif
                        
                        @if(isset($post->bedrooms) && $post->bedrooms)
                        <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl">
                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-lg text-purple-600 dark:text-purple-400">hotel</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $post->bedrooms }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Phòng ngủ</p>
                            </div>
                        </div>
                        @endif
                        
                        @if(isset($post->bathrooms) && $post->bathrooms)
                        <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl">
                            <div class="w-10 h-10 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-lg text-cyan-600 dark:text-cyan-400">bathroom</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $post->bathrooms }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Phòng tắm</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </header>
                
                <!-- =====================================================
                     DESCRIPTION
                ===================================================== -->
                @if($post->description)
                <section class="bg-white dark:bg-slate-800 rounded-2xl p-6 lg:p-8 shadow-lg border border-slate-200 dark:border-slate-700">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-teal-500">description</span>
                        Mô tả
                    </h2>
                    <div class="prose prose-sm dark:prose-invert max-w-none text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                        {{ $post->description }}
                    </div>
                </section>
                @endif
                
                <!-- =====================================================
                     SOURCE INFO
                ===================================================== -->
                @if($post->source_group || $post->facebook_url)
                <section class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-6 lg:p-8 border border-slate-200 dark:border-slate-700">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-teal-500">source</span>
                        Nguồn tin
                    </h2>
                    <div class="flex flex-wrap gap-4">
                        @if($post->source_group)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <span class="text-blue-600 dark:text-blue-400 font-bold text-lg">f</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $post->source_group }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Facebook Group</p>
                            </div>
                        </div>
                        @endif
                        @if($post->author_name)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center">
                                <span class="material-symbols-outlined text-teal-600 dark:text-teal-400">person</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $post->author_name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Người đăng</p>
                            </div>
                        </div>
                        @endif
                    </div>
                    @if($post->facebook_url)
                    <a href="{{ $post->facebook_url }}" target="_blank" rel="noopener noreferrer" 
                       class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-medium transition-colors">
                        <span class="material-symbols-outlined">open_in_new</span>
                        <span>Xem bài viết gốc</span>
                    </a>
                    @endif
                </section>
                @endif
            </div>
            
            <!-- =====================================================
                 SIDEBAR (1/3)
            ===================================================== -->
            <aside class="lg:col-span-1">
                <div class="sticky top-24 space-y-6">
                    
                    <!-- =====================================================
                         FEATURES
                    ===================================================== -->
                    @if($post->features && count($post->features) > 0)
                    <section class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-teal-500">check_circle</span>
                            Đặc điểm nổi bật
                        </h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($post->features as $feature)
                            <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 rounded-xl text-sm font-medium">
                                <span class="material-symbols-outlined text-base text-teal-500">check</span>
                                {{ $feature->name }}
                            </span>
                            @endforeach
                        </div>
                    </section>
                    @endif
                    
                    <!-- =====================================================
                         PROPERTY DETAILS
                    ===================================================== -->
                    <section class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-teal-500">info</span>
                            Thông tin chi tiết
                        </h2>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="text-slate-500 dark:text-slate-400">Loại BĐS</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ $post->property_type ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="text-slate-500 dark:text-slate-400">Diện tích</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ $post->area_formatted ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="text-slate-500 dark:text-slate-400">Mặt tiền</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ $post->frontage_count > 0 ? $post->frontage_count . ' mặt' : 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="text-slate-500 dark:text-slate-400">Chiều sâu</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ $post->depth_text ?? 'N/A' }}</span>
                            </div>
                            @if($post->ward)
                            <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="text-slate-500 dark:text-slate-400">Phường/Xã</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ $post->ward }}</span>
                            </div>
                            @endif
                            @if($post->district)
                            <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="text-slate-500 dark:text-slate-400">Quận/Huyện</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ $post->district }}</span>
                            </div>
                            @endif
                            <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="text-slate-500 dark:text-slate-400">Tỉnh/TP</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ $post->city ?? 'Thái Nguyên' }}</span>
                            </div>
                            @if($post->direction)
                            <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="text-slate-500 dark:text-slate-400">Hướng</span>
                                <span class="font-medium text-slate-900 dark:text-white capitalize">{{ str_replace('-', ' ', $post->direction) }}</span>
                            </div>
                            @endif
                        </div>
                    </section>
                    
                    <!-- =====================================================
                         CONTACT
                    ===================================================== -->
                    <section class="bg-gradient-to-br from-teal-500 to-teal-600 rounded-2xl p-6 shadow-lg text-white">
                        <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined">contact_phone</span>
                            Liên hệ
                        </h2>
                        
                        @if($post->contacts && count($post->contacts) > 0)
                        <div class="space-y-3 mb-3">
                            @foreach($post->contacts as $contact)
                            <a href="tel:{{ $contact->phone }}" 
                               class="flex items-center gap-3 p-3 bg-white/20 hover:bg-white/30 rounded-xl transition-colors group">
                                <span class="material-symbols-outlined text-white group-hover:scale-110 transition-transform">phone</span>
                                <span class="font-semibold">{{ $contact->phone }}</span>
                            </a>
                            @endforeach
                        </div>
                        @else
                        <p class="text-white/80 text-center py-4">
                            Chưa có thông tin liên hệ
                        </p>
                        @endif
                        @if($post->contacts && count($post->contacts) > 0)
                        <div class="space-y-3">
                            @foreach($post->contacts as $contact)
                            <a href="https://zalo.me/{{ $contact->phone }}" 
                               class="flex items-center gap-3 p-3 bg-white/20 hover:bg-white/30 rounded-xl transition-colors group">
                                {{-- <span class="material-symbols-outlined text-white group-hover:scale-110 transition-transform">phone</span> --}}
                                <span><img src="https://stc-zpl.zdn.vn/favicon.ico" alt="Zalo" class="w-4 h-4 group-hover:scale-110 transition-transform"></span>
                                <span class="font-semibold">{{ $contact->phone }}</span>
                            </a>
                            @endforeach
                        </div>
                        @else
                        <p class="text-white/80 text-center py-4">
                            Chưa có thông tin liên hệ
                        </p>
                        @endif
                        
                        <!-- Action Buttons -->
                        {{-- <div class="space-y-3">
                            <button onclick="savePost()" class="w-full py-3 bg-white text-teal-600 font-semibold rounded-xl flex items-center justify-center gap-2 hover:bg-slate-100 transition-colors">
                                <span class="material-symbols-outlined save-icon">favorite_border</span>
                                <span class="filled-icon hidden material-symbols-outlined text-red-500">favorite</span>
                                <span class="save-text">Lưu tin</span>
                            </button>
                            
                            <button onclick="sharePost()" class="w-full py-3 bg-white/20 hover:bg-white/30 text-white font-semibold rounded-xl flex items-center justify-center gap-2 transition-colors">
                                <span class="material-symbols-outlined">share</span>
                                <span>Chia sẻ</span>
                            </button>
                        </div> --}}
                    </section>
                    
                    <!-- Post Meta -->
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-4 border border-slate-200 dark:border-slate-700">
                        <div class="space-y-3 text-sm">
                            @if($post->published_at)
                            <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                                <span class="material-symbols-outlined text-base">schedule</span>
                                <span>Đăng: {{ $post->published_at->format('d/m/Y H:i') }}</span>
                            </div>
                            @endif
                            @if($post->views)
                            <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                                <span class="material-symbols-outlined text-base">visibility</span>
                                <span>{{ $post->views }} lượt xem</span>
                            </div>
                            @endif
                            @if($post->ai_confidence ?? $post->confidence)
                            <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                                <span class="material-symbols-outlined text-base">auto_awesome</span>
                                <span>AI confidence: {{ round(($post->ai_confidence ?? $post->confidence) * 100) }}%</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Report -->
                    <button class="w-full py-2.5 text-sm text-slate-500 dark:text-slate-400 hover:text-red-500 transition-colors flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-base">flag</span>
                        <span>Báo cáo tin không hợp lệ</span>
                    </button>
                </div>
            </aside>
        </div>
    </div>
</article>

<!-- =====================================================
     IMAGE GALLERY MODAL (Lightbox)
===================================================== -->
<div id="galleryModal" class="fixed inset-0 z-[80] hidden bg-black/95" role="dialog" aria-modal="true" aria-label="Gallery">
    <!-- Close button -->
    <button onclick="closeGallery()" class="absolute top-4 right-4 z-10 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors" aria-label="Đóng">
        <span class="material-symbols-outlined text-2xl">close</span>
    </button>
    
    <!-- Counter -->
    <div class="absolute top-4 left-1/2 -translate-x-1/2 z-10 px-4 py-2 bg-white/10 rounded-full text-white text-sm font-medium">
        <span id="galleryCounter">1 / 1</span>
    </div>
    
    <!-- Main Image Container -->
    <div class="absolute inset-0 flex items-center justify-center p-16">
        <div id="galleryImageContainer" class="relative max-w-full max-h-full">
            <img id="galleryImage" src="" alt="" class="max-w-full max-h-[calc(100vh-160px)] object-contain rounded-lg">
            
            <!-- Navigation Arrows -->
            <button onclick="prevImage()" id="prevBtn" class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors" aria-label="Ảnh trước">
                <span class="material-symbols-outlined text-2xl">chevron_left</span>
            </button>
            <button onclick="nextImage()" id="nextBtn" class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors" aria-label="Ảnh sau">
                <span class="material-symbols-outlined text-2xl">chevron_right</span>
            </button>
        </div>
    </div>
    
    <!-- Thumbnail Strip -->
    @if(isset($post->images) && count($post->images) > 1)
    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 p-2 bg-black/50 rounded-2xl max-w-[90vw] overflow-x-auto scrollbar-hide">
        @foreach($post->images as $index => $image)
        <button onclick="goToImage({{ $index }})" 
                class="gallery-thumb flex-shrink-0 w-16 h-12 rounded-lg overflow-hidden border-2 border-transparent hover:border-white transition-all opacity-60 hover:opacity-100 {{ $index === 0 ? 'border-white opacity-100' : '' }}">
            <img src="{{ $image }}" alt="Thumbnail {{ $index + 1 }}" class="w-full h-full object-cover">
        </button>
        @endforeach
    </div>
    @endif
    
    <!-- Download button -->
    <a id="downloadBtn" href="" download class="absolute bottom-4 right-4 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors" aria-label="Tải ảnh">
        <span class="material-symbols-outlined text-xl">download</span>
    </a>
</div>

<style>
    #galleryImageContainer img {
        transition: transform 0.2s ease-out;
    }
    #galleryImageContainer.zoomed img {
        cursor: grab;
    }
    #galleryImageContainer.zoomed img:active {
        cursor: grabbing;
    }
</style>

@push('scripts')
<script>
    // =====================================================
    // IMAGE GALLERY / LIGHTBOX
    // =====================================================
    const images = @json($post->images ?? [$post->image_url]);
    let currentIndex = 0;
    let zoomLevel = 1;
    
    function openGallery(index) {
        if (!images || images.length === 0) return;
        
        currentIndex = index;
        updateGalleryImage();
        
        document.getElementById('galleryModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        preloadAdjacentImages();
    }
    
    function changeMainImage(index) {
        if (!images || images.length === 0) return;
        
        // Update main image
        const mainImg = document.getElementById('mainImage');
        if (mainImg) {
            mainImg.src = images[index];
        }
        
        // Update thumbnail borders
        document.querySelectorAll('.thumbnail-btn').forEach((btn, i) => {
            btn.classList.toggle('border-teal-500', i === index);
            btn.classList.toggle('border-transparent', i !== index);
        });
        
        // Update image counter
        const counter = document.querySelector('#mainImageContainer .absolute.bottom-4.right-4 span:last-child');
        if (counter) {
            counter.textContent = `${index + 1} / ${images.length}`;
        }
    }
    
    function closeGallery() {
        document.getElementById('galleryModal').classList.add('hidden');
        document.body.style.overflow = '';
        resetZoom();
    }
    
    function updateGalleryImage() {
        const img = document.getElementById('galleryImage');
        const counter = document.getElementById('galleryCounter');
        const downloadBtn = document.getElementById('downloadBtn');
        const thumbs = document.querySelectorAll('.gallery-thumb');
        
        img.src = images[currentIndex];
        img.alt = 'Hình ảnh {{ $post->title ?? "BĐS" }} ' + (currentIndex + 1);
        counter.textContent = `${currentIndex + 1} / ${images.length}`;
        downloadBtn.href = images[currentIndex];
        
        thumbs.forEach((thumb, i) => {
            thumb.classList.toggle('border-white', i === currentIndex);
            thumb.classList.toggle('opacity-100', i === currentIndex);
            thumb.classList.toggle('opacity-60', i !== currentIndex);
        });
        
        document.getElementById('prevBtn').style.display = images.length > 1 ? 'flex' : 'none';
        document.getElementById('nextBtn').style.display = images.length > 1 ? 'flex' : 'none';
        
        resetZoom();
    }
    
    function nextImage() {
        if (currentIndex < images.length - 1) {
            currentIndex++;
            updateGalleryImage();
            preloadAdjacentImages();
        }
    }
    
    function prevImage() {
        if (currentIndex > 0) {
            currentIndex--;
            updateGalleryImage();
            preloadAdjacentImages();
        }
    }
    
    function goToImage(index) {
        currentIndex = index;
        updateGalleryImage();
        preloadAdjacentImages();
    }
    
    function preloadAdjacentImages() {
        [currentIndex - 1, currentIndex + 1].forEach(i => {
            if (i >= 0 && i < images.length) {
                const img = new Image();
                img.src = images[i];
            }
        });
    }
    
    function resetZoom() {
        zoomLevel = 1;
        const img = document.getElementById('galleryImage');
        const container = document.getElementById('galleryImageContainer');
        img.style.transform = 'scale(1)';
        container.classList.remove('zoomed');
    }
    
    // Double click to zoom
    document.getElementById('galleryImageContainer')?.addEventListener('dblclick', () => {
        if (zoomLevel > 1) {
            resetZoom();
            zoomLevel = 1;
        } else {
            zoomLevel = 2;
            const img = document.getElementById('galleryImage');
            img.style.transform = `scale(${zoomLevel})`;
            document.getElementById('galleryImageContainer').classList.add('zoomed');
        }
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (document.getElementById('galleryModal').classList.contains('hidden')) return;
        
        switch(e.key) {
            case 'Escape':
                closeGallery();
                break;
            case 'ArrowLeft':
                prevImage();
                break;
            case 'ArrowRight':
                nextImage();
                break;
        }
    });
    
    // =====================================================
    // SAVE POST
    // =====================================================
    function savePost() {
        const postId = {{ $post->id ?? 'null' }};
        if (!postId) return;
        
        const saved = JSON.parse(localStorage.getItem('savedPosts') || '[]');
        const btn = event.currentTarget;
        const saveIcon = btn.querySelector('.save-icon');
        const filledIcon = btn.querySelector('.filled-icon');
        const text = btn.querySelector('.save-text');
        
        const index = saved.indexOf(postId);
        if (index === -1) {
            saved.push(postId);
            saveIcon?.classList.add('hidden');
            filledIcon?.classList.remove('hidden');
            text.textContent = 'Đã lưu';
            btn.classList.add('bg-red-50');
            showToast('Đã lưu tin này!', 'success');
        } else {
            saved.splice(index, 1);
            saveIcon?.classList.remove('hidden');
            filledIcon?.classList.add('hidden');
            text.textContent = 'Lưu tin';
            btn.classList.remove('bg-red-50');
            showToast('Đã bỏ lưu tin', 'info');
        }
        
        localStorage.setItem('savedPosts', JSON.stringify(saved));
    }
    
    document.addEventListener('DOMContentLoaded', () => {
        const postId = {{ $post->id ?? 'null' }};
        if (postId) {
            const saved = JSON.parse(localStorage.getItem('savedPosts') || '[]');
            if (saved.includes(postId)) {
                const btn = document.querySelector('[onclick="savePost()"]');
                if (btn) {
                    btn.querySelector('.save-icon')?.classList.add('hidden');
                    btn.querySelector('.filled-icon')?.classList.remove('hidden');
                    btn.querySelector('.save-text').textContent = 'Đã lưu';
                    btn.classList.add('bg-red-50');
                }
            }
        }
    });
    
    // =====================================================
    // SHARE POST
    // =====================================================
    function sharePost() {
        const url = window.location.href;
        const title = '{{ addslashes($post->title ?? "Bất động sản") }}';
        
        if (navigator.share) {
            navigator.share({
                title: title,
                text: 'Xem bất động sản: ' + title,
                url: url
            });
        } else {
            navigator.clipboard.writeText(url);
            showToast('Đã copy link chia sẻ!', 'success');
        }
    }
    
    // =====================================================
    // TOAST
    // =====================================================
    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer') || createToastContainer();
        const toast = document.createElement('div');
        
        const icons = { success: 'check_circle', error: 'error', info: 'info', warning: 'warning' };
        const colors = { success: 'text-emerald-500', error: 'text-red-500', info: 'text-blue-500', warning: 'text-amber-500' };
        
        toast.className = 'toast pointer-events-auto';
        toast.innerHTML = `
            <span class="material-symbols-outlined ${colors[type]}">${icons[type]}</span>
            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">${message}</span>
            <button onclick="this.parentElement.remove()" class="ml-2 text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        `;
        
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = 'toastIn 0.3s ease-out reverse';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
    
    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'fixed bottom-6 right-6 z-[100] flex flex-col gap-3 pointer-events-none';
        document.body.appendChild(container);
        return container;
    }
    
    // Initialize main image click handler
    document.getElementById('mainImageContainer')?.addEventListener('click', () => openGallery(0));
    
    // Thumbnail click handlers
    document.querySelectorAll('.thumbnail-btn').forEach((btn, i) => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const mainImg = document.getElementById('mainImage');
            if (images[i]) {
                mainImg.src = images[i];
            }
            document.querySelectorAll('.thumbnail-btn').forEach(b => b.classList.remove('border-teal-500'));
            btn.classList.add('border-teal-500');
        });
    });
</script>
@endpush
@endsection
