{{-- Property Card Component - Modern Real Estate Style --}}
{{-- Usage: @include('public.components.property-card', ['post' => $post, 'index' => $index]) --}}

@php
    $postId = $post->id ?? 'unknown';
    $title = $post->title ?? 'Không có tiêu đề';
    $price = $post->price_formatted ?? ($post->price_value ? number_format($post->price_value / 1000000) . ' triệu' : 'Thỏa thuận');
    $area = $post->area_formatted ?? $post->area_value ?? 'N/A';
    $address = $post->address_text ?? $post->ward ?? $post->district ?? 'Thái Nguyên';
    $type = $post->property_type ?? 'Bất động sản';
    $image = $post->image_url ?? null;
    $views = $post->views ?? rand(50, 500);
    $createdAt = isset($post->published_at) ? $post->published_at->diffForHumans() : 'Vừa đăng';
    $hasMultipleImages = isset($post->images) && count($post->images) > 1;
    
    // Badges
    $isNew = isset($post->published_at) && $post->published_at->diffInDays() < 3;
    $isFeatured = $post->is_featured ?? false;
    $isOwner = $post->is_owner ?? false;
    
    // Detail URL
    $detailUrl = route('search.post.view', [
        'token' => base64_encode($postId)
    ]);
    
    // Animation delay
    $animationDelay = ($index ?? 0) * 100;
@endphp

<article 
    class="group bg-white dark:bg-slate-800 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:border-teal-300 dark:hover:border-teal-600 transition-all duration-300 hover:shadow-xl hover:shadow-teal-500/10 hover:-translate-y-1"
    style="animation-delay: {{ $animationDelay }}ms;"
    data-post-id="{{ $postId }}"
>
    <!-- Image Section -->
    <div class="relative aspect-[4/3] overflow-hidden">
        <!-- Image -->
        <a href="{{ $detailUrl }}" class="block w-full h-full">
            @if($image)
                <img 
                    src="{{ $image }}" 
                    alt="{{ $title }}"
                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                    loading="lazy"
                >
            @else
                <div class="w-full h-full bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-800 flex items-center justify-center">
                    <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600">landscape</span>
                </div>
            @endif
        </a>
        
        <!-- Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        
        <!-- Top Badges -->
        <div class="absolute top-3 left-3 flex flex-wrap gap-2">
            @if($isNew)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-500 text-white rounded-full text-xs font-semibold shadow-lg">
                    <span class="material-symbols-outlined text-xs">fiber_new</span>
                    <span>Mới</span>
                </span>
            @endif
            
            @if($isFeatured)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-500 text-white rounded-full text-xs font-semibold shadow-lg">
                    <span class="material-symbols-outlined text-xs">star</span>
                    <span>Nổi bật</span>
                </span>
            @endif
            
            @if($isOwner)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-500 text-white rounded-full text-xs font-semibold shadow-lg">
                    <span class="material-symbols-outlined text-xs">verified</span>
                    <span>Chính chủ</span>
                </span>
            @endif
        </div>
        
        <!-- Top Right Actions -->
        <div class="absolute top-3 right-3 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <button 
                onclick="toggleSave({{ $postId }}, this)"
                class="w-9 h-9 rounded-full bg-white/90 dark:bg-slate-800/90 hover:bg-white dark:hover:bg-slate-800 flex items-center justify-center shadow-lg transition-all hover:scale-110"
                aria-label="Lưu tin"
            >
                <span class="material-symbols-outlined text-lg save-icon text-slate-500 hover:text-red-500">favorite_border</span>
                <span class="material-symbols-outlined text-lg filled-icon text-red-500 hidden">favorite</span>
            </button>
            
            <button 
                onclick="sharePost('{{ $detailUrl }}', '{{ addslashes($title) }}')"
                class="w-9 h-9 rounded-full bg-white/90 dark:bg-slate-800/90 hover:bg-white dark:hover:bg-slate-800 flex items-center justify-center shadow-lg transition-all hover:scale-110"
                aria-label="Chia sẻ"
            >
                <span class="material-symbols-outlined text-lg text-slate-500">share</span>
            </button>
        </div>
        
        <!-- Image Count -->
        @if($hasMultipleImages)
        <div class="absolute bottom-3 right-3 px-2 py-1 bg-black/60 rounded-md text-white text-xs font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">photo_library</span>
            <span>{{ count($post->images ?? []) }}</span>
        </div>
        @endif
        
        <!-- Price Badge (always visible) -->
        <div class="absolute bottom-3 left-3">
            <span class="inline-block px-3 py-1.5 bg-teal-500 text-white rounded-lg text-sm font-bold shadow-lg">
                {{ $price }}
            </span>
        </div>
    </div>
    
    <!-- Content Section -->
    <div class="p-4">
        <!-- Title -->
        <a href="{{ $detailUrl }}" class="block mb-2">
            <h3 class="text-base lg:text-lg font-semibold text-slate-900 dark:text-white line-clamp-2 hover:text-teal-600 dark:hover:text-teal-400 transition-colors leading-tight">
                {{ $title }}
            </h3>
        </a>
        
        <!-- Address -->
        <div class="flex items-start gap-2 text-sm text-slate-500 dark:text-slate-400 mb-4">
            <span class="material-symbols-outlined text-base flex-shrink-0 mt-0.5">location_on</span>
            <span class="line-clamp-2">{{ $address }}</span>
        </div>
        
        <!-- Meta Row -->
        <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-4 text-xs text-slate-400 dark:text-slate-500">
                <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">schedule</span>
                    <span>{{ $createdAt }}</span>
                </span>
                <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">visibility</span>
                    <span>{{ $views }}</span>
                </span>
            </div>
            
            <!-- Property Type -->
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700/50 px-2.5 py-1 rounded-lg">
                {{ $type }}
            </span>
        </div>
        
        <!-- Quick Info Row -->
        <div class="flex items-center gap-4 mt-3">
            <div class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-400">
                <span class="material-symbols-outlined text-emerald-500 text-base">square_foot</span>
                <span>{{ $area }}</span>
            </div>
            @if($post->frontage_count > 0)
            <div class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-400">
                <span class="material-symbols-outlined text-blue-500 text-base">straighten</span>
                <span>{{ $post->frontage_count }}m</span>
            </div>
            @endif
            @if($post->direction)
            <div class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-400">
                <span class="material-symbols-outlined text-amber-500 text-base">explore</span>
                <span class="capitalize">{{ str_replace('-', ' ', $post->direction) }}</span>
            </div>
            @endif
        </div>
    </div>
</article>
