{{-- Empty State Component - Modern Real Estate Style --}}
{{-- Usage: @include('public.components.empty-state', ['title' => 'No results', 'message' => 'Try adjusting filters']) --}}

@php
    $title = $title ?? 'Không có kết quả';
    $message = $message ?? 'Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm.';
    $showFilters = $showFilters ?? false;
    $icon = $icon ?? 'search_off';
@endphp

<div class="flex flex-col items-center justify-center py-16 px-6 text-center animate-fade-in">
    <!-- Icon -->
    <div class="w-24 h-24 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-6">
        <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-600">{{ $icon }}</span>
    </div>
    
    <!-- Title -->
    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">
        {{ $title }}
    </h3>
    
    <!-- Message -->
    <p class="text-slate-500 dark:text-slate-400 max-w-md mb-8">
        {{ $message }}
    </p>
    
    <!-- Actions -->
    <div class="flex flex-wrap justify-center gap-3">
        @if($showFilters)
            <a href="{{ route('search') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                <span class="material-symbols-outlined">filter_alt_off</span>
                <span>Xóa bộ lọc</span>
            </a>
        @endif
        
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-teal-500 to-teal-600 text-white font-semibold rounded-xl hover:from-teal-600 hover:to-teal-700 transition-all shadow-lg shadow-teal-500/25">
            <span class="material-symbols-outlined">search</span>
            <span>Tìm kiếm khác</span>
        </a>
    </div>
    
    <!-- Suggestions -->
    <div class="mt-10 w-full max-w-lg">
        <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">Đề xuất tìm kiếm:</p>
        <div class="flex flex-wrap justify-center gap-2">
            <a href="{{ route('search', ['property_type' => 'Đất nền/Đất thổ cư']) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-full text-sm font-medium hover:border-teal-400 hover:text-teal-600 dark:hover:text-teal-400 transition-all">
                <span class="material-symbols-outlined text-emerald-500 text-base">terrain</span>
                <span>Đất nền</span>
            </a>
            <a href="{{ route('search', ['property_type' => 'Nhà phố/Nhà riêng']) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-full text-sm font-medium hover:border-teal-400 hover:text-teal-600 dark:hover:text-teal-400 transition-all">
                <span class="material-symbols-outlined text-blue-500 text-base">house</span>
                <span>Nhà phố</span>
            </a>
            <a href="{{ route('search', ['property_type' => 'Căn hộ chung cư']) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-full text-sm font-medium hover:border-teal-400 hover:text-teal-600 dark:hover:text-teal-400 transition-all">
                <span class="material-symbols-outlined text-purple-500 text-base">apartment</span>
                <span>Chung cư</span>
            </a>
            <a href="{{ route('search', ['price_max' => 1000000000]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-full text-sm font-medium hover:border-teal-400 hover:text-teal-600 dark:hover:text-teal-400 transition-all">
                <span class="material-symbols-outlined text-amber-500 text-base">payments</span>
                <span>Dưới 1 tỷ</span>
            </a>
        </div>
    </div>
</div>
