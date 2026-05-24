@extends('layouts.app')

@section('content')
<!-- Page Header with Search -->
<section class="pt-24 pb-8 lg:pt-32 lg:pb-12 bg-gradient-to-b from-slate-50 to-white dark:from-slate-900 dark:to-slate-900">
    <div class="container mx-auto px-4 lg:px-6">
        <!-- Search Bar -->
        <div class="max-w-5xl mx-auto mb-8">
            <form action="{{ route('search') }}" method="GET" id="mainSearchForm" class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-4 lg:p-6 border border-slate-200 dark:border-slate-700">
                <div class="flex flex-col lg:flex-row gap-4">
                    <!-- Main Search Input -->
                    <div class="flex-1 relative">
                        <label for="keyword" class="sr-only">Từ khóa tìm kiếm</label>
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                        <input type="text" id="keyword" name="keyword" value="{{ $keyword ?? request('keyword') }}"
                            class="w-full pl-12 pr-4 py-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                            placeholder="Tìm theo địa điểm, loại BĐS, đặc điểm...">
                    </div>
                    
                    <!-- Mobile Filters Toggle -->
                    <button type="button" onclick="toggleMobileFilters()" class="lg:hidden flex items-center justify-center gap-2 px-6 py-4 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-medium">
                        <span class="material-symbols-outlined">tune</span>
                        <span>Bộ lọc</span>
                    </button>
                    
                    <!-- Submit -->
                    <button type="submit" class="px-8 py-4 bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-teal-500/25 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">search</span>
                        <span>Tìm kiếm</span>
                    </button>
                </div>
                
                <!-- Quick Filters -->
                <div class="flex flex-wrap gap-2 mt-4">
                    <a href="{{ route('search', array_merge(request()->except('property_type'), ['keyword' => $keyword ?? '', 'property_type' => 'Đất nền/Đất thổ cư'])) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium transition-all {{ request('property_type') == 'Đất nền/Đất thổ cư' ? 'bg-teal-500 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-teal-100 dark:hover:bg-teal-900/30' }}">
                        <span class="material-symbols-outlined text-emerald-500">terrain</span>
                        <span>Đất nền</span>
                    </a>
                    <a href="{{ route('search', array_merge(request()->except('property_type'), ['keyword' => $keyword ?? '', 'property_type' => 'Nhà phố/Nhà riêng'])) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium transition-all {{ request('property_type') == 'Nhà phố/Nhà riêng' ? 'bg-teal-500 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-teal-100 dark:hover:bg-teal-900/30' }}">
                        <span class="material-symbols-outlined text-blue-500">house</span>
                        <span>Nhà phố</span>
                    </a>
                    <a href="{{ route('search', array_merge(request()->except('property_type'), ['keyword' => $keyword ?? '', 'property_type' => 'Căn hộ chung cư'])) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium transition-all {{ request('property_type') == 'Căn hộ chung cư' ? 'bg-teal-500 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-teal-100 dark:hover:bg-teal-900/30' }}">
                        <span class="material-symbols-outlined text-purple-500">apartment</span>
                        <span>Chung cư</span>
                    </a>
                    <a href="{{ route('search', array_merge(request()->except('price'), ['keyword' => $keyword ?? '', 'price_min' => 0, 'price_max' => 1000000000])) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium transition-all {{ request('price_max') == 1000000000 ? 'bg-teal-500 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-teal-100 dark:hover:bg-teal-900/30' }}">
                        <span class="material-symbols-outlined text-amber-500">payments</span>
                        <span>Dưới 1 tỷ</span>
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Active Filters -->
        @if(_hasActiveFilters(request()))
            <div class="max-w-5xl mx-auto">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm text-slate-500 dark:text-slate-400">Lọc:</span>
                    
                    @if (request('property_type'))
                        <a href="{{ _removeFilter(request(), 'property_type') }}"
                            class="inline-flex items-center gap-2 px-3 py-1.5 bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 rounded-full text-sm font-medium border border-teal-200 dark:border-teal-700 hover:bg-teal-100 transition-colors">
                            {{ request('property_type') }}
                            <span class="material-symbols-outlined text-base">close</span>
                        </a>
                    @endif
                    
                    @if (request('price_min') || request('price_max'))
                        <a href="{{ _removeFilter(request(), 'price_min', 'price_max') }}"
                            class="inline-flex items-center gap-2 px-3 py-1.5 bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 rounded-full text-sm font-medium border border-teal-200 dark:border-teal-700 hover:bg-teal-100 transition-colors">
                            @if (request('price_min') && request('price_max'))
                                {{ _formatPrice(request('price_min')) }} - {{ _formatPrice(request('price_max')) }}
                            @elseif(request('price_min'))
                                Từ {{ _formatPrice(request('price_min')) }}
                            @else
                                Đến {{ _formatPrice(request('price_max')) }}
                            @endif
                            <span class="material-symbols-outlined text-base">close</span>
                        </a>
                    @endif
                    
                    @if (request('area_min') || request('area_max'))
                        <a href="{{ _removeFilter(request(), 'area_min', 'area_max') }}"
                            class="inline-flex items-center gap-2 px-3 py-1.5 bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 rounded-full text-sm font-medium border border-teal-200 dark:border-teal-700 hover:bg-teal-100 transition-colors">
                            @if (request('area_min') && request('area_max'))
                                {{ request('area_min') }}m² - {{ request('area_max') }}m²
                            @elseif(request('area_min'))
                                Từ {{ request('area_min') }}m²
                            @else
                                Đến {{ request('area_max') }}m²
                            @endif
                            <span class="material-symbols-outlined text-base">close</span>
                        </a>
                    @endif
                    
                    <a href="{{ route('search', ['keyword' => $keyword ?? '']) }}"
                        class="text-sm text-red-500 hover:text-red-600 font-medium ml-2">
                        Xóa tất cả
                    </a>
                </div>
            </div>
        @endif
    </div>
</section>

<!-- Main Content -->
<section class="py-8 lg:py-12">
    <div class="container mx-auto px-4 lg:px-6">
        <div class="flex gap-8">
            
            <!-- Results Area -->
            <div class="flex-1 min-w-0">
                
                <!-- Results Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                    <div>
                        @if ($keyword ?? request('keyword'))
                            <h1 class="text-2xl lg:text-3xl font-bold text-slate-900 dark:text-white mb-2">
                                Kết quả cho "<span class="text-teal-600 dark:text-teal-400">{{ $keyword ?? request('keyword') }}</span>"
                            </h1>
                        @else
                            <h1 class="text-2xl lg:text-3xl font-bold text-slate-900 dark:text-white mb-2">
                                Tất cả bất động sản
                            </h1>
                        @endif
                        <p class="text-slate-600 dark:text-slate-400 flex items-center gap-2">
                            <span class="material-symbols-outlined">home</span>
                            {{ $posts->total() }} bất động sản được tìm thấy
                        </p>
                    </div>
                    
                    <!-- Sort & View Toggle -->
                    <div class="flex items-center gap-3">
                        <form action="{{ route('search') }}" method="GET" id="sortForm" class="flex items-center gap-2">
                            <input type="hidden" name="keyword" value="{{ $keyword ?? request('keyword') }}">
                            @foreach (request()->except('sort', 'page') as $key => $value)
                                @if ($value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <select name="sort" onchange="document.getElementById('sortForm').submit()"
                                class="px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 cursor-pointer">
                                <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>Mới nhất</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Giá thấp → cao</option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Giá cao → thấp</option>
                                <option value="area_desc" {{ request('sort') == 'area_desc' ? 'selected' : '' }}>Diện tích lớn nhất</option>
                                <option value="views" {{ request('sort') == 'views' ? 'selected' : '' }}>Nhiều lượt xem</option>
                            </select>
                        </form>
                        
                        <!-- View Toggle (Desktop) -->
                        <div class="hidden lg:flex items-center gap-1 p-1.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                            <button onclick="setView('grid')" id="gridViewBtn"
                                class="p-2.5 rounded-lg bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 transition-all shadow-sm"
                                aria-label="Xem lưới">
                                <span class="material-symbols-outlined text-lg">grid_view</span>
                            </button>
                            <button onclick="setView('list')" id="listViewBtn"
                                class="p-2.5 rounded-lg transition-colors text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700"
                                aria-label="Xem danh sách">
                                <span class="material-symbols-outlined text-lg">view_list</span>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Results Grid -->
                @if ($posts->count() > 0)
                    <div id="listingsContainer" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach ($posts as $index => $post)
                            @include('public.components.property-card', ['post' => $post, 'index' => $index])
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    @if ($posts->hasPages())
                        <div class="mt-12 flex justify-center">
                            {{ $posts->withQueryString()->onEachSide(1)->links('vendor.pagination.tailwind') }}
                        </div>
                    @endif
                @elseif(($keyword ?? request('keyword')) || _hasActiveFilters(request()))
                    @include('public.components.empty-state', [
                        'title' => 'Không tìm thấy kết quả',
                        'message' => 'Thử thay đổi từ khóa hoặc bộ lọc để có kết quả tốt hơn.',
                        'showFilters' => true,
                    ])
                @else
                    <div class="text-center py-20">
                        <div class="w-24 h-24 mx-auto mb-6 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-600">home</span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Chưa có tin rao nào</h3>
                        <p class="text-slate-500 dark:text-slate-400 mb-8">Hệ thống đang cập nhật dữ liệu. Vui lòng quay lại sau.</p>
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-teal-500 to-teal-600 text-white font-semibold rounded-xl hover:from-teal-600 hover:to-teal-700 transition-all shadow-lg shadow-teal-500/25">
                            <span class="material-symbols-outlined">home</span>
                            <span>Về trang chủ</span>
                        </a>
                    </div>
                @endif
            </div>
            
            <!-- Sidebar Filters (Desktop) -->
            <aside id="filterSidebar" class="hidden lg:block w-80 flex-shrink-0">
                <div class="sticky top-24">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <!-- Header -->
                        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50">
                            <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-teal-500">tune</span>
                                Bộ lọc
                            </h3>
                            <a href="{{ route('search', ['keyword' => $keyword ?? '']) }}"
                                class="text-sm text-teal-600 dark:text-teal-400 hover:underline font-medium">
                                Xóa tất cả
                            </a>
                        </div>
                        
                        <!-- Filters -->
                        <form action="{{ route('search') }}" method="GET" id="filterForm">
                            <input type="hidden" name="keyword" value="{{ $keyword ?? '' }}">
                            
                            <div class="p-6 space-y-6 max-h-[calc(100vh-250px)] overflow-y-auto">
                                
                                <!-- Property Type -->
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Loại bất động sản</h4>
                                    <div class="space-y-2">
                                        @foreach ($propertyTypes ?? [] as $type)
                                            <label class="flex items-center gap-3 cursor-pointer group">
                                                <input type="radio" name="property_type" value="{{ $type }}"
                                                    {{ request('property_type') == $type ? 'checked' : '' }}
                                                    class="w-4 h-4 text-teal-600 border-slate-300 dark:border-slate-600 focus:ring-teal-500">
                                                <span class="text-sm text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white transition-colors">{{ $type }}</span>
                                            </label>
                                        @endforeach
                                        <label class="flex items-center gap-3 cursor-pointer group">
                                            <input type="radio" name="property_type" value=""
                                                {{ !request('property_type') ? 'checked' : '' }}
                                                class="w-4 h-4 text-teal-600 border-slate-300 dark:border-slate-600 focus:ring-teal-500">
                                            <span class="text-sm text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white transition-colors">Tất cả</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Price Range -->
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Mức giá</h4>
                                    <div class="grid grid-cols-2 gap-3">
                                        <input type="number" name="price_min" value="{{ request('price_min') }}"
                                            placeholder="Từ (triệu)" class="px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                        <input type="number" name="price_max" value="{{ request('price_max') }}"
                                            placeholder="Đến (triệu)" class="px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    </div>
                                    <div class="flex flex-wrap gap-2 mt-3">
                                        <button type="button" onclick="setPriceFilter(0, 500)" class="px-3 py-1.5 text-xs rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-teal-100 dark:hover:bg-teal-900/30 transition-colors">Dưới 500tr</button>
                                        <button type="button" onclick="setPriceFilter(500, 1000)" class="px-3 py-1.5 text-xs rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-teal-100 dark:hover:bg-teal-900/30 transition-colors">500tr-1tỷ</button>
                                        <button type="button" onclick="setPriceFilter(1000, 2000)" class="px-3 py-1.5 text-xs rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-teal-100 dark:hover:bg-teal-900/30 transition-colors">1-2 tỷ</button>
                                        <button type="button" onclick="setPriceFilter(2000, 5000)" class="px-3 py-1.5 text-xs rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-teal-100 dark:hover:bg-teal-900/30 transition-colors">2-5 tỷ</button>
                                    </div>
                                </div>
                                
                                <!-- Area Range -->
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Diện tích</h4>
                                    <div class="grid grid-cols-2 gap-3">
                                        <input type="number" name="area_min" value="{{ request('area_min') }}"
                                            placeholder="Từ (m²)" class="px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                        <input type="number" name="area_max" value="{{ request('area_max') }}"
                                            placeholder="Đến (m²)" class="px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    </div>
                                </div>
                                
                                <!-- Direction -->
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Hướng nhà</h4>
                                    <select name="direction" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                        <option value="">Tất cả hướng</option>
                                        <option value="dong" {{ request('direction') == 'dong' ? 'selected' : '' }}>Hướng Đông</option>
                                        <option value="tay" {{ request('direction') == 'tay' ? 'selected' : '' }}>Hướng Tây</option>
                                        <option value="nam" {{ request('direction') == 'nam' ? 'selected' : '' }}>Hướng Nam</option>
                                        <option value="bac" {{ request('direction') == 'bac' ? 'selected' : '' }}>Hướng Bắc</option>
                                        <option value="dong-bac" {{ request('direction') == 'dong-bac' ? 'selected' : '' }}>Đông Bắc</option>
                                        <option value="tay-bac" {{ request('direction') == 'tay-bac' ? 'selected' : '' }}>Tây Bắc</option>
                                        <option value="dong-nam" {{ request('direction') == 'dong-nam' ? 'selected' : '' }}>Đông Nam</option>
                                        <option value="tay-nam" {{ request('direction') == 'tay-nam' ? 'selected' : '' }}>Tây Nam</option>
                                    </select>
                                </div>
                                
                                <!-- Legal -->
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Pháp lý</h4>
                                    <select name="legal" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                        <option value="">Tất cả</option>
                                        <option value="so-do" {{ request('legal') == 'so-do' ? 'selected' : '' }}>Sổ đỏ</option>
                                        <option value="so-hong" {{ request('legal') == 'so-hong' ? 'selected' : '' }}>Sổ hồng</option>
                                        <option value="dang-cho" {{ request('legal') == 'dang-cho' ? 'selected' : '' }}>Đang chờ</option>
                                    </select>
                                </div>
                                
                                <!-- Amenities -->
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Tiện ích</h4>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="checkbox" name="mat_tien" value="1" {{ request('mat_tien') ? 'checked' : '' }}
                                                class="w-4 h-4 text-teal-600 rounded border-slate-300 dark:border-slate-600 focus:ring-teal-500">
                                            <span class="text-sm text-slate-600 dark:text-slate-400">Mặt tiền</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="checkbox" name="ngo_o_to" value="1" {{ request('ngo_o_to') ? 'checked' : '' }}
                                                class="w-4 h-4 text-teal-600 rounded border-slate-300 dark:border-slate-600 focus:ring-teal-500">
                                            <span class="text-sm text-slate-600 dark:text-slate-400">Ngõ ô tô</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="checkbox" name="gan_truong" value="1" {{ request('gan_truong') ? 'checked' : '' }}
                                                class="w-4 h-4 text-teal-600 rounded border-slate-300 dark:border-slate-600 focus:ring-teal-500">
                                            <span class="text-sm text-slate-600 dark:text-slate-400">Gần trường học</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="checkbox" name="gan_kcn" value="1" {{ request('gan_kcn') ? 'checked' : '' }}
                                                class="w-4 h-4 text-teal-600 rounded border-slate-300 dark:border-slate-600 focus:ring-teal-500">
                                            <span class="text-sm text-slate-600 dark:text-slate-400">Gần KCN Samsung</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Footer -->
                            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex gap-3 bg-slate-50 dark:bg-slate-800/50">
                                <a href="{{ route('search', ['keyword' => $keyword ?? '']) }}"
                                    class="flex-1 px-4 py-3 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-medium rounded-xl text-center hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors">
                                    Xóa
                                </a>
                                <button type="submit" class="flex-1 px-4 py-3 bg-gradient-to-r from-teal-500 to-teal-600 text-white font-medium rounded-xl hover:from-teal-600 hover:to-teal-700 transition-all shadow-lg shadow-teal-500/25">
                                    Áp dụng
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<!-- Mobile Filter Bottom Sheet -->
<div id="mobileFilterSheet" class="fixed inset-0 z-[70] hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="toggleMobileFilters()"></div>
    <div class="absolute bottom-0 left-0 right-0 bg-white dark:bg-slate-800 rounded-t-3xl max-h-[85vh] overflow-hidden animate-slide-up">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 px-6 py-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Bộ lọc</h3>
            <button onclick="toggleMobileFilters()" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <!-- Content -->
        <div class="overflow-y-auto max-h-[calc(85vh-140px)] p-6">
            <form action="{{ route('search') }}" method="GET" id="mobileFilterForm">
                <input type="hidden" name="keyword" value="{{ $keyword ?? '' }}">
                
                <!-- Property Type -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Loại bất động sản</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($propertyTypes ?? [] as $type)
                            <button type="button" onclick="toggleMobileChip(this, 'property_type', '{{ $type }}')"
                                class="px-4 py-2 rounded-full text-sm font-medium transition-all {{ request('property_type') == $type ? 'bg-teal-500 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                                {{ $type }}
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="property_type" id="mobile_property_type" value="{{ request('property_type') }}">
                </div>
                
                <!-- Price -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Mức giá</h4>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="setMobilePrice(this, 0, 500)" class="px-4 py-2 rounded-full text-sm font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">Dưới 500tr</button>
                        <button type="button" onclick="setMobilePrice(this, 500, 1000)" class="px-4 py-2 rounded-full text-sm font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">500tr-1tỷ</button>
                        <button type="button" onclick="setMobilePrice(this, 1000, 2000)" class="px-4 py-2 rounded-full text-sm font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">1-2 tỷ</button>
                    </div>
                    <div class="flex gap-3 mt-3">
                        <input type="number" name="price_min" value="{{ request('price_min') }}"
                            placeholder="Từ (triệu)" class="flex-1 px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm">
                        <input type="number" name="price_max" value="{{ request('price_max') }}"
                            placeholder="Đến (triệu)" class="flex-1 px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm">
                    </div>
                </div>
                
                <!-- Area -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Diện tích</h4>
                    <div class="flex gap-3">
                        <input type="number" name="area_min" value="{{ request('area_min') }}"
                            placeholder="Từ (m²)" class="flex-1 px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm">
                        <input type="number" name="area_max" value="{{ request('area_max') }}"
                            placeholder="Đến (m²)" class="flex-1 px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm">
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Footer -->
        <div class="sticky bottom-0 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 px-6 py-4 flex gap-3">
            <a href="{{ route('search', ['keyword' => $keyword ?? '']) }}" class="flex-1 px-4 py-3 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-medium rounded-xl text-center">Xóa</a>
            <button type="submit" form="mobileFilterForm" class="flex-1 px-4 py-3 bg-gradient-to-r from-teal-500 to-teal-600 text-white font-medium rounded-xl">Áp dụng</button>
        </div>
    </div>
</div>

<style>
    .pagination {
        @apply flex items-center justify-center gap-1;
    }
    .pagination a, .pagination span {
        @apply px-4 py-2 rounded-xl text-sm font-medium transition-colors;
    }
    .pagination a {
        @apply text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700;
    }
    .pagination span.current {
        @apply bg-teal-500 text-white border border-teal-500;
    }
</style>
@endsection

@push('scripts')
<script>
    // View Toggle
    function setView(view) {
        const container = document.getElementById('listingsContainer');
        const gridBtn = document.getElementById('gridViewBtn');
        const listBtn = document.getElementById('listViewBtn');
        
        if (view === 'grid') {
            container.className = 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6';
            gridBtn.classList.add('bg-teal-50', 'dark:bg-teal-900/30', 'text-teal-600', 'dark:text-teal-400', 'shadow-sm');
            gridBtn.classList.remove('text-slate-500');
            listBtn.classList.remove('bg-teal-50', 'dark:bg-teal-900/30', 'text-teal-600', 'dark:text-teal-400', 'shadow-sm');
            listBtn.classList.add('text-slate-500');
        } else {
            container.className = 'flex flex-col gap-4';
            listBtn.classList.add('bg-teal-50', 'dark:bg-teal-900/30', 'text-teal-600', 'dark:text-teal-400', 'shadow-sm');
            listBtn.classList.remove('text-slate-500');
            gridBtn.classList.remove('bg-teal-50', 'dark:bg-teal-900/30', 'text-teal-600', 'dark:text-teal-400', 'shadow-sm');
            gridBtn.classList.add('text-slate-500');
        }
    }
    
    // Mobile Filters
    function toggleMobileFilters() {
        const sheet = document.getElementById('mobileFilterSheet');
        sheet.classList.toggle('hidden');
        document.body.style.overflow = sheet.classList.contains('hidden') ? '' : 'hidden';
    }
    
    function toggleMobileChip(btn, inputName, value) {
        const input = document.getElementById('mobile_' + inputName);
        const allChips = btn.parentElement.querySelectorAll('button');
        
        allChips.forEach(c => {
            if (c !== btn) {
                c.classList.remove('bg-teal-500', 'text-white');
                c.classList.add('bg-slate-100', 'dark:bg-slate-700', 'text-slate-700', 'dark:text-slate-300');
            }
        });
        
        if (btn.classList.contains('bg-teal-500')) {
            btn.classList.remove('bg-teal-500', 'text-white');
            btn.classList.add('bg-slate-100', 'dark:bg-slate-700', 'text-slate-700', 'dark:text-slate-300');
            input.value = '';
        } else {
            btn.classList.add('bg-teal-500', 'text-white');
            btn.classList.remove('bg-slate-100', 'dark:bg-slate-700', 'text-slate-700', 'dark:text-slate-300');
            input.value = value;
        }
    }
    
    function setMobilePrice(btn, min, max) {
        const form = document.getElementById('mobileFilterForm');
        form.querySelector('[name="price_min"]').value = min;
        form.querySelector('[name="price_max"]').value = max;
    }
    
    function setPriceFilter(min, max) {
        document.querySelector('[name="price_min"]').value = min;
        document.querySelector('[name="price_max"]').value = max;
    }
    
    // Escape key closes modals
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.getElementById('mobileFilterSheet')?.classList.add('hidden');
            document.body.style.overflow = '';
        }
    });
</script>
@endpush
