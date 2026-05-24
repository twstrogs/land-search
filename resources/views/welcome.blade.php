<!DOCTYPE html>
<html lang="vi" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Land Search - Tìm kiếm Bất động sản Thái Nguyên')</title>
    <meta name="description"
        content="Tìm kiếm nhà đất, đất nền, chung cư tại Thái Nguyên. Hàng nghìn tin rao được cập nhật liên tục. Tra cứu nhanh chóng, miễn phí.">
    <meta name="keywords" content="bất động sản thái nguyên, nhà đất thái nguyên, đất nền, chung cư, mua bán nhà đất">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%230d9488' rx='20' width='100' height='100'/><path fill='white' d='M50 20L20 45v35h25V60h10v20h25V45L50 20z'/></svg>">
    <!-- Open Graph -->
    <meta property="og:title" content="Land Search - Tìm kiếm Bất động sản Thái Nguyên">
    <meta property="og:description" content="Nền tảng tra cứu bất động sản thông minh tại Thái Nguyên với AI">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="vi_VN">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Material Symbols -->
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght@20..48,100..700&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('styles')
</head>

<body class="bg-white dark:bg-dark-base text-secondary-800 dark:text-secondary-100 antialiased">

    <!-- Skip to content (Accessibility) -->
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2 focus:bg-primary-600 focus:text-white focus:rounded-lg">
        Skip to main content
    </a>

    <!-- =====================================================
         NAVIGATION BAR
    ===================================================== -->
    {{-- <header role="banner" id="header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
        <nav class="glass border-b border-secondary-100/50 dark:border-secondary-800/50" role="navigation" aria-label="Main navigation">
            <div class="container mx-auto px-4 lg:px-6">
                <div class="flex items-center justify-between h-16 lg:h-20">
                    
                    <!-- Logo -->
                    <a href="/" class="flex items-center gap-3 group" aria-label="Land Search - Trang chủ">
                        <div class="w-10 h-10 rounded-xl gradient-primary flex items-center justify-center shadow-lg shadow-primary-500/25 group-hover:shadow-primary-500/40 transition-shadow">
                            <span class="material-symbols-outlined text-white text-xl">home</span>
                        </div>
                        <div class="hidden sm:block">
                            <span class="text-xl font-bold gradient-text">Land Search</span>
                            <span class="hidden lg:block text-xs text-secondary-500 dark:text-secondary-400 -mt-1">Thái Nguyên</span>
                        </div>
                    </a>
                    
                    <!-- Desktop Navigation -->
                    <div class="hidden lg:flex items-center gap-1">
                        <a href="/" class="px-4 py-2 rounded-lg text-sm font-medium text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20">Trang chủ</a>
                        <a href="{{ route('search') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-secondary-600 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">Tìm kiếm</a>
                        <a href="#map" class="px-4 py-2 rounded-lg text-sm font-medium text-secondary-600 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">Bản đồ</a>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex items-center gap-2 lg:gap-3">
                        <!-- Search Toggle (Mobile) -->
                        <button onclick="toggleSearch()" class="lg:hidden p-2.5 rounded-xl bg-secondary-100 dark:bg-secondary-800 text-secondary-600 dark:text-secondary-300 hover:bg-secondary-200 dark:hover:bg-secondary-700 transition-colors" aria-label="Tìm kiếm">
                            <span class="material-symbols-outlined">search</span>
                        </button>
                        
                        <!-- Theme Toggle -->
                        <button id="themeToggle" class="p-2.5 rounded-xl bg-secondary-100 dark:bg-secondary-800 text-secondary-600 dark:text-secondary-300 hover:bg-secondary-200 dark:hover:bg-secondary-700 transition-colors" aria-label="Chuyển đổi giao diện sáng/tối">
                            <span class="material-symbols-outlined dark:hidden">dark_mode</span>
                            <span class="material-symbols-outlined hidden dark:block">light_mode</span>
                        </button>
                        
                        <!-- Auth Buttons -->
                        <div class="hidden sm:flex items-center gap-2">
                            @auth
                                <a href="{{ route('dashboard.index') }}" class="btn-primary">
                                    <span class="material-symbols-outlined text-lg">dashboard</span>
                                    <span>Dashboard</span>
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="px-4 py-2.5 text-sm font-medium text-secondary-700 dark:text-secondary-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                    Đăng nhập
                                </a>
                                <a href="{{ route('register') }}" class="btn-primary">
                                    <span class="material-symbols-outlined text-lg">person_add</span>
                                    <span>Đăng ký</span>
                                </a>
                            @endauth
                        </div>
                        
                        <!-- Mobile Menu Toggle -->
                        <button id="mobileMenuToggle" class="lg:hidden p-2.5 rounded-xl bg-secondary-100 dark:bg-secondary-800 text-secondary-600 dark:text-secondary-300 hover:bg-secondary-200 dark:hover:bg-secondary-700 transition-colors" aria-label="Menu" aria-expanded="false">
                            <span class="material-symbols-outlined menu-icon">menu</span>
                            <span class="material-symbols-outlined close-icon hidden">close</span>
                        </button>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Mobile Menu Drawer -->
        <div id="mobileMenu" class="lg:hidden hidden fixed inset-0 top-16 bg-white dark:bg-dark-base z-40" role="dialog" aria-modal="true">
            <div class="container mx-auto px-4 py-6 space-y-4">
                <a href="/" class="block px-4 py-3 rounded-xl bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 font-semibold">
                    Trang chủ
                </a>
                <a href="{{ route('search') }}" class="block px-4 py-3 rounded-xl text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 font-medium">
                    Tìm kiếm BĐS
                </a>
                <a href="#map" class="block px-4 py-3 rounded-xl text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 font-medium">
                    Xem bản đồ
                </a>
                <hr class="border-secondary-200 dark:border-secondary-800 my-4">
                @guest
                <a href="{{ route('login') }}" class="block px-4 py-3 rounded-xl text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 font-medium">
                    Đăng nhập
                </a>
                <a href="{{ route('register') }}" class="block w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl text-center">
                    Đăng ký miễn phí
                </a>
                @else
                <a href="{{ route('dashboard.index') }}" class="block w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl text-center">
                    Dashboard
                </a>
                @endguest
            </div>
        </div>
    </header> --}}
    @include('public.components.header')
    <!-- =====================================================
         MAIN CONTENT
    ===================================================== -->
    <main id="main-content" role="main">

        <!-- =====================================================
             HERO SECTION - SUPER SMART SEARCH
        ===================================================== -->
        <section class="relative min-h-[90vh] lg:min-h-[85vh] flex items-center overflow-hidden"
            aria-labelledby="hero-heading">
            <!-- Background Image -->
            <img src="{{ asset('background.jpg') }}" alt="" class="absolute inset-0 w-full h-full object-cover" style="image-rendering: -webkit-optimize-contrast; image-rendering: crisp-edges;  transform: scale(1.05);" aria-hidden="true">
            <!-- Background Overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-teal-600/70 via-teal-700/75 to-teal-800/80"></div>

            <div class="container mx-auto px-4 lg:px-6 pt-24 lg:pt-28 pb-16 relative z-10">
                <div class="max-w-4xl mx-auto text-center">
                    <!-- Badge -->
                    <div
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/20 backdrop-blur-sm text-white text-sm font-medium mb-6 animate-fade-up border border-white/30">
                        <span class="material-symbols-outlined text-lg bg-gradient-to-r from-teal-600 to-teal-500 bg-clip-text text-transparent">auto_awesome</span>
                        <span class="bg-gradient-to-r from-teal-600 to-teal-500 bg-clip-text text-transparent">AI-powered Smart Search</span>
                    </div>

                    <!-- Headline -->
                    <h1 id="hero-heading"
                        class="text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight mb-5 animate-fade-up animation-delay-100 text-neutral-400">
                        <span class="text-6xl font-bold bg-gradient-to-r from-teal-600 to-teal-500 bg-clip-text text-transparent">Bất động sản Thái Nguyên</span>
                        <br>
                        <br>
                        <br>
                        {{-- <span class="text-amber-300">thông minh hơn</span> --}}
                        {{-- <br> --}}
                        {{-- <span class="text-xl sm:text-2xl lg:text-3xl text-white/90">tại
                            Thái Nguyên</span> --}}
                    </h1>

                    <!-- Subheadline -->
                    {{-- <p
                        class="text-base lg:text-lg text-white/80 max-w-2xl mx-auto mb-8 animate-fade-up animation-delay-200 text-balance">
                        Khám phá hàng nghìn tin rao nhà đất tại Thái Nguyên. Tìm theo khu vực, mức giá, diện tích hoặc
                        đặc điểm riêng biệt.
                    </p> --}}
                </div>

                <!-- =====================================================
                     SMART SEARCH BAR
                ===================================================== -->
                <div class="max-w-5xl mx-auto mt-8 animate-fade-up animation-delay-300" id="heroSearch">
                    <form action="{{ route('search') }}" method="GET" id="searchForm"
                        class="bg-white/20 backdrop-blur-sm rounded-3xl shadow-elevated p-3 lg:p-4 border border-white/50 dark:border-secondary-800/50">
                        <div class="flex flex-col lg:flex-row gap-3">
                            <!-- Location -->
                            <div class="flex-1 relative group">
                                <label for="location" class="sr-only">Địa điểm</label>
                                <div
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-secondary-400 group-focus-within:text-primary-500 transition-colors">
                                    <span class="material-symbols-outlined">location_on</span>
                                </div>
                                <input type="text" id="location" name="location" value="{{ request('location') }}"
                                    class="w-full pl-11 pr-4 py-4 bg-secondary-50 dark:bg-secondary-800/50 rounded-2xl text-secondary-800 dark:text-secondary-100 placeholder:text-secondary-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-dark-surface transition-all"
                                    placeholder="Phường, đường, khu vực..." autocomplete="off">
                            </div>

                            <!-- Price Range -->
                            <div class="relative group lg:w-48">
                                <label for="price" class="sr-only">Mức giá</label>
                                <div
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-secondary-400 group-focus-within:text-primary-500 transition-colors">
                                    <span class="material-symbols-outlined">payments</span>
                                </div>
                                <select id="price" name="price"
                                    class="w-full pl-11 pr-10 py-4 bg-secondary-50 dark:bg-secondary-800/50 rounded-2xl text-secondary-800 dark:text-secondary-100 appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-dark-surface transition-all">
                                    <option value="">Mức giá</option>
                                    <option value="0-500000000"
                                        {{ request('price') == '0-500000000' ? 'selected' : '' }}>Dưới 500 triệu
                                    </option>
                                    <option value="500000000-1000000000"
                                        {{ request('price') == '500000000-1000000000' ? 'selected' : '' }}>500 triệu - 1
                                        tỷ</option>
                                    <option value="1000000000-2000000000"
                                        {{ request('price') == '1000000000-2000000000' ? 'selected' : '' }}>1 - 2 tỷ
                                    </option>
                                    <option value="2000000000-5000000000"
                                        {{ request('price') == '2000000000-5000000000' ? 'selected' : '' }}>2 - 5 tỷ
                                    </option>
                                    <option value="5000000000-10000000000"
                                        {{ request('price') == '5000000000-10000000000' ? 'selected' : '' }}>5 - 10 tỷ
                                    </option>
                                    <option value="10000000000+"
                                        {{ request('price') == '10000000000+' ? 'selected' : '' }}>Trên 10 tỷ</option>
                                </select>
                                <div
                                    class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-secondary-400">
                                    <span class="material-symbols-outlined text-lg">expand_more</span>
                                </div>
                            </div>

                            <!-- Area -->
                            <div class="relative group lg:w-44">
                                <label for="area" class="sr-only">Diện tích</label>
                                <div
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-secondary-400 group-focus-within:text-primary-500 transition-colors">
                                    <span class="material-symbols-outlined">square_foot</span>
                                </div>
                                <select id="area" name="area"
                                    class="w-full pl-11 pr-10 py-4 bg-secondary-50 dark:bg-secondary-800/50 rounded-2xl text-secondary-800 dark:text-secondary-100 appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-dark-surface transition-all">
                                    <option value="">Diện tích</option>
                                    <option value="0-50" {{ request('area') == '0-50' ? 'selected' : '' }}>Dưới 50m²
                                    </option>
                                    <option value="50-100" {{ request('area') == '50-100' ? 'selected' : '' }}>50 -
                                        100m²</option>
                                    <option value="100-200" {{ request('area') == '100-200' ? 'selected' : '' }}>100 -
                                        200m²</option>
                                    <option value="200-500" {{ request('area') == '200-500' ? 'selected' : '' }}>200 -
                                        500m²</option>
                                    <option value="500+" {{ request('area') == '500+' ? 'selected' : '' }}>Trên
                                        500m²</option>
                                </select>
                                <div
                                    class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-secondary-400">
                                    <span class="material-symbols-outlined text-lg">expand_more</span>
                                </div>
                            </div>

                            <!-- Property Type -->
                            <div class="relative group lg:w-44">
                                <label for="type" class="sr-only">Loại BĐS</label>
                                <div
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-secondary-400 group-focus-within:text-primary-500 transition-colors">
                                    <span class="material-symbols-outlined">apartment</span>
                                </div>
                                <select id="type" name="type"
                                    class="w-full pl-11 pr-10 py-4 bg-secondary-50 dark:bg-secondary-800/50 rounded-2xl text-secondary-800 dark:text-secondary-100 appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-500 focus:bg-white dark:focus:bg-dark-surface transition-all">
                                    <option value="">Loại BĐS</option>
                                    <option value="nha-pho" {{ request('type') == 'nha-pho' ? 'selected' : '' }}>Nhà
                                        phố</option>
                                    <option value="dat-nen" {{ request('type') == 'dat-nen' ? 'selected' : '' }}>Đất
                                        nền</option>
                                    <option value="chung-cu" {{ request('type') == 'chung-cu' ? 'selected' : '' }}>
                                        Chung cư</option>
                                    <option value="biet-thu" {{ request('type') == 'biet-thu' ? 'selected' : '' }}>
                                        Biệt thự</option>
                                </select>
                                <div
                                    class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-secondary-400">
                                    <span class="material-symbols-outlined text-lg">expand_more</span>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn-primary px-8 py-4 rounded-2xl flex-shrink-0 flex flex-row items-center gap-2">
                                <span class="material-symbols-outlined text-white">search</span>
                                <span class="hidden sm:inline text-white">Tìm kiếm</span>
                            </button>
                        </div>

                        <!-- Advanced Filters Toggle -->
                        <div class="mt-3 pt-3 border-t border-secondary-100 dark:border-secondary-800">
                            <button type="button" onclick="toggleAdvancedFilters()"
                                class="text-sm text-secondary-500 dark:text-secondary-400 hover:text-primary-600 dark:hover:text-primary-400 flex items-center gap-1 transition-colors">
                                <span class="material-symbols-outlined text-lg">tune</span>
                                <span class="text-white">Bộ lọc nâng cao</span>
                                <span class="material-symbols-outlined text-lg advanced-icon">expand_more</span>
                                <span class="material-symbols-outlined text-lg advanced-icon hidden">expand_less</span>
                            </button>

                            <!-- Advanced Filters Panel -->
                            <div id="advancedFilters"
                                class="hidden mt-4 pt-4 border-t border-secondary-100 dark:border-secondary-800">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <!-- Direction -->
                                    <div class="relative">
                                        <select name="direction" class="p-2 input pr-10 text-sm rounded-xl bg-white/50 dark:bg-black/30 backdrop-blur-sm">
                                            <option value="">Hướng nhà</option>
                                            <option value="dong">Hướng Đông</option>
                                            <option value="tay">Hướng Tây</option>
                                            <option value="nam">Hướng Nam</option>
                                            <option value="bac">Hướng Bắc</option>
                                            <option value="dong-bac">Đông Bắc</option>
                                            <option value="tay-bac">Tây Bắc</option>
                                            <option value="dong-nam">Đông Nam</option>
                                            <option value="tay-nam">Tây Nam</option>
                                        </select>
                                    </div>

                                    <!-- Legal -->
                                    <div class="relative">
                                        <select name="legal" class="p-2 input pr-10 text-sm rounded-xl bg-white/50 dark:bg-black/30 backdrop-blur-sm">
                                            <option value="">Pháp lý</option>
                                            <option value="so-do">Sổ đỏ</option>
                                            <option value="so-hong">Sổ hồng</option>
                                            <option value="dang-cho">Đang chờ</option>
                                        </select>
                                    </div>

                                    <!-- Bedrooms -->
                                    <div class="relative">
                                        <select name="bedrooms" class="p-2 input pr-10 text-sm rounded-xl bg-white/50 dark:bg-black/30 backdrop-blur-sm">
                                            <option value="">Số phòng ngủ</option>
                                            <option value="1">1+ PN</option>
                                            <option value="2">2+ PN</option>
                                            <option value="3">3+ PN</option>
                                            <option value="4">4+ PN</option>
                                            <option value="5">5+ PN</option>
                                        </select>
                                    </div>

                                    <!-- Amenities -->
                                    <div class="relative">
                                        <select name="amenities" class="p-2 input pr-10 text-sm rounded-xl bg-white/50 dark:bg-black/30 backdrop-blur-sm">
                                            <option value="">Tiện ích</option>
                                            <option value="mat-tien">Mặt tiền</option>
                                            <option value="ngo-o-to">Ngõ ô tô</option>
                                            <option value="gan-truong">Gần trường học</option>
                                            <option value="gan-kcn">Gần KCN Samsung</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- =====================================================
                     QUICK FILTER CHIPS
                ===================================================== -->
                {{-- <div class="flex flex-wrap justify-center gap-2 lg:gap-3 mt-6 animate-fade-up animation-delay-400">
                    <a href="{{ route('search', ['price' => '0-1000000000']) }}" class="filter-chip">
                        <span class="material-symbols-outlined text-base text-amber-500">payments</span>
                        <span>Dưới 1 tỷ</span>
                    </a>
                    <a href="{{ route('search', ['type' => 'dat-nen']) }}" class="filter-chip">
                        <span class="material-symbols-outlined text-base text-emerald-500">terrain</span>
                        <span>Đất nền</span>
                    </a>
                    <a href="{{ route('search', ['type' => 'chung-cu']) }}" class="filter-chip">
                        <span class="material-symbols-outlined text-base text-blue-500">apartment</span>
                        <span>Chung cư</span>
                    </a>
                    <a href="{{ route('search', ['location' => 'Samsung']) }}" class="filter-chip">
                        <span class="material-symbols-outlined text-base text-purple-500">factory</span>
                        <span>Gần Samsung</span>
                    </a>
                    <a href="{{ route('search', ['amenities' => 'mat-tien']) }}" class="filter-chip">
                        <span class="material-symbols-outlined text-base text-orange-500">home</span>
                        <span>Mặt đường</span>
                    </a>
                    <a href="{{ route('search', ['amenities' => 'ngo-o-to']) }}" class="filter-chip">
                        <span class="material-symbols-outlined text-base text-cyan-500">directions_car</span>
                        <span>Ngõ ô tô</span>
                    </a>
                </div> --}}
            </div>

            <!-- Scroll indicator -->
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce hidden lg:block">
                <a href="#listings"
                    class="flex flex-col items-center text-secondary-400 dark:text-secondary-500 hover:text-primary-500 transition-colors"
                    aria-label="Cuộn xuống">
                    <span class="text-xs font-medium mb-1 text-white">Khám phá</span>
                    <span class="material-symbols-outlined">keyboard_arrow_down</span>
                </a>
            </div>
        </section>

        <!-- =====================================================
             STATS BAR
        ===================================================== -->
        <section class="relative -mt-8 z-20" aria-label="Thống kê">
            <div class="container mx-auto px-4 lg:px-6">
                <div
                    class="bg-white dark:bg-dark-surface rounded-2xl shadow-elevated p-4 lg:p-6 border border-secondary-100 dark:border-secondary-800/50">
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-8">
                        <div class="text-center">
                            <div class="text-2xl lg:text-3xl font-bold gradient-text mb-1">
                                {{ number_format($stats['total_posts'] ?? 2450) }}+</div>
                            <div class="text-sm text-secondary-500 dark:text-secondary-400">Tin rao BĐS</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl lg:text-3xl font-bold gradient-text mb-1">
                                {{ number_format($stats['total_wards'] ?? 72) }}+</div>
                            <div class="text-sm text-secondary-500 dark:text-secondary-400">Phường/Xã</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl lg:text-3xl font-bold gradient-text mb-1">24/7</div>
                            <div class="text-sm text-secondary-500 dark:text-secondary-400">Cập nhật tự động</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl lg:text-3xl font-bold gradient-text mb-1">100%</div>
                            <div class="text-sm text-secondary-500 dark:text-secondary-400">Miễn phí tra cứu</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =====================================================
             FEATURED LISTINGS
        ===================================================== -->
        <section id="listings" class="py-16 lg:py-24" aria-labelledby="listings-heading">
            <div class="container mx-auto px-4 lg:px-6">
                <!-- Section Header -->
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8 lg:mb-10">
                    <div>
                        <h2 id="listings-heading"
                            class="text-xl lg:text-2xl font-bold text-secondary-900 dark:text-white">
                            Bất động sản nổi bật
                        </h2>
                        <p class="text-secondary-500 dark:text-secondary-400 mt-1">Những tin rao được quan tâm nhiều
                            nhất</p>
                    </div>
                    <a href="{{ route('search') }}"
                        class="inline-flex items-center gap-2 text-primary-600 dark:text-primary-400 font-medium hover:gap-3 transition-all">
                        <span>Xem tất cả</span>
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </a>
                </div>

                <!-- =====================================================
                     STICKY FILTER BAR (Desktop)
                ===================================================== -->
                {{-- <div id="stickyFilterBar"
                    class="sticky top-16 lg:top-20 z-30 mb-6 hidden lg:block opacity-0 -translate-y-2 transition-all duration-300">
                    <div
                        class="bg-white dark:bg-dark-surface rounded-2xl shadow-medium p-3 border border-secondary-100 dark:border-secondary-800/50">
                        <div class="flex items-center gap-3">
                            <!-- Quick Filters -->
                            <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide">
                                <button class="filter-chip flex-shrink-0 filter-chip-active">
                                    <span class="material-symbols-outlined text-base">home</span>
                                    <span>Tất cả</span>
                                </button>
                                <button class="filter-chip flex-shrink-0">
                                    <span class="material-symbols-outlined text-base text-emerald-500">terrain</span>
                                    <span>Đất nền</span>
                                </button>
                                <button class="filter-chip flex-shrink-0">
                                    <span class="material-symbols-outlined text-base text-blue-500">house</span>
                                    <span>Nhà phố</span>
                                </button>
                                <button class="filter-chip flex-shrink-0">
                                    <span class="material-symbols-outlined text-base text-purple-500">apartment</span>
                                    <span>Chung cư</span>
                                </button>
                                <button class="filter-chip flex-shrink-0">
                                    <span class="material-symbols-outlined text-base text-amber-500">payments</span>
                                    <span>Giảm giá</span>
                                </button>
                            </div>

                            <div class="flex-1"></div>

                            <!-- Sort -->
                            <div class="relative">
                                <select id="sortSelect" class="input pr-10 py-2.5 text-sm w-auto cursor-pointer">
                                    <option value="latest">Mới nhất</option>
                                    <option value="price_asc">Giá thấp → cao</option>
                                    <option value="price_desc">Giá cao → thấp</option>
                                    <option value="area_desc">Diện tích lớn nhất</option>
                                    <option value="views">Nhiều lượt xem</option>
                                </select>
                                <div
                                    class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-secondary-400">
                                    <span class="material-symbols-outlined text-lg">expand_more</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}

                <!-- =====================================================
                     PROPERTY GRID
                ===================================================== -->
                @if (isset($posts) && $posts->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach ($posts as $index => $post)
                            @include('public.components.property-card', [
                                'post' => $post,
                                'index' => $index,
                            ])
                        @endforeach
                    </div>

                    <!-- Load More / Pagination -->
                    @if ($posts->hasPages())
                        <div class="mt-12 text-center">
                            {{ $posts->withQueryString()->links('vendor.pagination.simple-tailwind') }}
                        </div>
                    @endif
                @else
                    <!-- Empty State -->
                    @include('public.components.empty-state', [
                        'title' => 'Chưa có tin rao nào',
                        'message' => 'Hệ thống đang cập nhật dữ liệu. Vui lòng quay lại sau.',
                        'showFilters' => true,
                    ])
                @endif
            </div>
        </section>

        <!-- =====================================================
             WHY CHOOSE US
        ===================================================== -->
        <section class="py-16 lg:py-24 bg-secondary-50 dark:bg-dark-surface/50" aria-labelledby="features-heading">
            <div class="container mx-auto px-4 lg:px-6">
                <div class="text-center max-w-2xl mx-auto mb-12 lg:mb-16">
                    <h2 id="features-heading"
                        class="text-xl lg:text-2xl font-bold text-secondary-900 dark:text-white mb-4">
                        Tại sao chọn Land Search?
                    </h2>
                    <p class="text-secondary-500 dark:text-secondary-400">
                        Nền tảng tra cứu bất động sản thông minh được xây dựng riêng cho thị trường Thái Nguyên
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                    <!-- Feature 1 -->
                    <div
                        class="group bg-white dark:bg-dark-surface rounded-2xl p-6 lg:p-8 shadow-card hover:shadow-elevated transition-all duration-300">
                        <div
                            class="w-14 h-14 rounded-2xl bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                            <span
                                class="material-symbols-outlined text-2xl text-primary-600 dark:text-primary-400">bolt</span>
                        </div>
                        <h3 class="text-lg font-bold text-secondary-900 dark:text-white mb-2">Tìm kiếm siêu nhanh</h3>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 leading-relaxed">
                            Chỉ vài giây để tìm được tin phù hợp với hàng nghìn tin rao được cập nhật liên tục.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div
                        class="group bg-white dark:bg-dark-surface rounded-2xl p-6 lg:p-8 shadow-card hover:shadow-elevated transition-all duration-300">
                        <div
                            class="w-14 h-14 rounded-2xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                            <span
                                class="material-symbols-outlined text-2xl text-amber-600 dark:text-amber-400">auto_awesome</span>
                        </div>
                        <h3 class="text-lg font-bold text-secondary-900 dark:text-white mb-2">AI thông minh</h3>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 leading-relaxed">
                            Trích xuất và chuẩn hóa thông tin tự động từ nhiều nguồn, đảm bảo dữ liệu chính xác.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div
                        class="group bg-white dark:bg-dark-surface rounded-2xl p-6 lg:p-8 shadow-card hover:shadow-elevated transition-all duration-300">
                        <div
                            class="w-14 h-14 rounded-2xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                            <span
                                class="material-symbols-outlined text-2xl text-emerald-600 dark:text-emerald-400">verified</span>
                        </div>
                        <h3 class="text-lg font-bold text-secondary-900 dark:text-white mb-2">Dữ liệu chuẩn</h3>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 leading-relaxed">
                            Thông tin được chuẩn hóa về giá, diện tích, vị trí giúp so sánh dễ dàng và chính xác.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div
                        class="group bg-white dark:bg-dark-surface rounded-2xl p-6 lg:p-8 shadow-card hover:shadow-elevated transition-all duration-300">
                        <div
                            class="w-14 h-14 rounded-2xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                            <span
                                class="material-symbols-outlined text-2xl text-blue-600 dark:text-blue-400">map</span>
                        </div>
                        <h3 class="text-lg font-bold text-secondary-900 dark:text-white mb-2">Bản đồ thông minh</h3>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 leading-relaxed">
                            Xem vị trí bất động sản trên bản đồ, tìm kiếm theo khu vực mong muốn.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- =====================================================
             CTA SECTION
        ===================================================== -->
        {{-- <section class="relative overflow-hidden py-40 lg:py-56 min-h-[700px] flex items-center"
            style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);" aria-labelledby="cta-heading">
            <!-- Background decoration -->
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-0 right-0 w-96 h-96 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2">
                </div>
                <div
                    class="absolute bottom-0 left-0 w-64 h-64 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2">
                </div>
            </div>

            <div class="container mx-auto px-4 lg:px-6 relative z-10">
                <div class="max-w-3xl mx-auto text-center">
                    <h2 id="cta-heading" class="text-4xl lg:text-5xl font-bold text-white mb-10">
                        Sẵn sàng tìm ngôi nhà mới?
                    </h2>

                    <div class="flex flex-col sm:flex-row justify-center gap-6">
                        <a href="{{ route('search') }}"
                            class="inline-flex items-center justify-center gap-2 px-10 py-5 text-lg font-semibold rounded-2xl transition-colors shadow-xl hover:scale-105 duration-300"
                            style="background-color: white; color: #0d9488;">
                            <span class="material-symbols-outlined">search</span>
                            <span>Tra cứu ngay</span>
                        </a>
                    </div>
                </div>
            </div>
        </section> --}}
    </main>

    <!-- =====================================================
         FOOTER
    ===================================================== -->
    <footer class="bg-secondary-900 dark:bg-dark-base py-12 lg:py-16" role="contentinfo">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12 mb-12">
                <!-- Brand -->
                <div class="lg:col-span-1">
                    <a href="/" class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl gradient-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-white text-xl">home</span>
                        </div>
                        <div>
                            <span class="text-xl font-bold text-white">Land Search</span>
                            <span class="block text-xs text-secondary-400">Thái Nguyên</span>
                        </div>
                    </a>
                    <p class="text-sm text-secondary-400 leading-relaxed">
                        Nền tảng tra cứu bất động sản thông minh tại Thái Nguyên. Tìm kiếm nhanh chóng, miễn phí.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-white font-semibold mb-4">Tra cứu nhanh</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('search', ['type' => 'dat-nen']) }}"
                                class="text-sm text-secondary-400 hover:text-white transition-colors">Đất nền</a></li>
                        <li><a href="{{ route('search', ['type' => 'nha-pho']) }}"
                                class="text-sm text-secondary-400 hover:text-white transition-colors">Nhà phố</a></li>
                        <li><a href="{{ route('search', ['type' => 'chung-cu']) }}"
                                class="text-sm text-secondary-400 hover:text-white transition-colors">Chung cư</a></li>
                        <li><a href="{{ route('search', ['price' => '0-1000000000']) }}"
                                class="text-sm text-secondary-400 hover:text-white transition-colors">Dưới 1 tỷ</a>
                        </li>
                    </ul>
                </div>

                <!-- Khu vực -->
                <div>
                    <h4 class="text-white font-semibold mb-4">Quận/Huyện</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('search', ['location' => 'TP Thái Nguyên']) }}"
                                class="text-sm text-secondary-400 hover:text-white transition-colors">TP. Thái
                                Nguyên</a></li>
                        <li><a href="{{ route('search', ['location' => 'Sông Công']) }}"
                                class="text-sm text-secondary-400 hover:text-white transition-colors">TX. Sông Công</a>
                        </li>
                        <li><a href="{{ route('search', ['location' => 'Đại Từ']) }}"
                                class="text-sm text-secondary-400 hover:text-white transition-colors">H. Đại Từ</a>
                        </li>
                        <li><a href="{{ route('search', ['location' => 'Phổ Yên']) }}"
                                class="text-sm text-secondary-400 hover:text-white transition-colors">H. Phổ Yên</a>
                        </li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-white font-semibold mb-4">Liên hệ</h4>
                    <ul class="space-y-2">
                        <li class="flex items-center gap-2 text-sm text-secondary-400">
                            <span class="material-symbols-outlined text-base">email</span>
                            <span>contact@landsearch.vn</span>
                        </li>
                        <li class="flex items-center gap-2 text-sm text-secondary-400">
                            <span class="material-symbols-outlined text-base">phone</span>
                            <span>0123 456 789</span>
                        </li>
                        <li class="flex items-center gap-2 text-sm text-secondary-400">
                            <span class="material-symbols-outlined text-base">location_on</span>
                            <span>Thái Nguyên, Việt Nam</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom -->
            <div
                class="pt-8 border-t border-secondary-800 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-sm text-secondary-500">
                    © {{ date('Y') }} Land Search. Mọi quyền được bảo lưu.
                </p>
                <div class="flex items-center gap-4">
                    <a href="#" class="text-secondary-500 hover:text-white transition-colors"
                        aria-label="Facebook">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                    </a>
                    <a href="#" class="text-secondary-500 hover:text-white transition-colors"
                        aria-label="YouTube">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- =====================================================
         QUICK VIEW MODAL
    ===================================================== -->
    <div id="quickViewModal" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true"
        aria-labelledby="quickViewTitle">
        <div class="modal-backdrop" onclick="closeQuickView()"></div>
        <div
            class="fixed left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-50 w-[calc(100%-2rem)] max-w-4xl max-h-[90vh] overflow-auto">
            <div class="bg-white dark:bg-dark-surface rounded-3xl shadow-elevated overflow-hidden animate-scale-in">
                <div class="relative">
                    <!-- Close button -->
                    <button onclick="closeQuickView()"
                        class="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center transition-colors"
                        aria-label="Đóng">
                        <span class="material-symbols-outlined">close</span>
                    </button>

                    <!-- Modal Content (loaded dynamically) -->
                    <div id="quickViewContent">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =====================================================
         TOAST CONTAINER
    ===================================================== -->
    <div id="toastContainer" class="fixed bottom-6 right-6 z-[100] flex flex-col gap-3 pointer-events-none"></div>

    <!-- =====================================================
         SCRIPTS
    ===================================================== -->
    <script>
        // =====================================================
        // THEME TOGGLE
        // =====================================================
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;

        // Always start with light mode default, respect saved preference
        html.classList.remove('dark');

        // Only add dark mode if explicitly saved as 'dark' in localStorage
        const savedTheme = localStorage.getItem('theme');

        if (savedTheme === 'dark') {
            html.classList.add('dark');
        }

        themeToggle?.addEventListener('click', () => {
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        });

        // =====================================================
        // MOBILE MENU
        // =====================================================
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        const menuIcon = mobileMenuToggle?.querySelector('.menu-icon');
        const closeIcon = mobileMenuToggle?.querySelector('.close-icon');

        mobileMenuToggle?.addEventListener('click', () => {
            const isOpen = !mobileMenu.classList.contains('hidden');
            mobileMenu.classList.toggle('hidden');
            menuIcon?.classList.toggle('hidden');
            closeIcon?.classList.toggle('hidden');
            mobileMenuToggle.setAttribute('aria-expanded', !isOpen);
            document.body.style.overflow = isOpen ? '' : 'hidden';
        });

        // =====================================================
        // ADVANCED FILTERS TOGGLE
        // =====================================================
        function toggleAdvancedFilters() {
            const panel = document.getElementById('advancedFilters');
            const icons = panel?.parentElement?.querySelectorAll('.advanced-icon');
            panel?.classList.toggle('hidden');
            icons?.forEach(icon => icon.classList.toggle('hidden'));
        }

        // =====================================================
        // STICKY HEADER ON SCROLL
        // =====================================================
        const header = document.getElementById('header');
        const stickyFilterBar = document.getElementById('stickyFilterBar');
        const heroSection = document.querySelector('.gradient-hero');

        const headerObserver = new IntersectionObserver(
            ([entry]) => {
                header.classList.toggle('header-scrolled', !entry.isIntersecting);
                if (stickyFilterBar) {
                    stickyFilterBar.classList.toggle('opacity-0', entry.isIntersecting);
                    stickyFilterBar.classList.toggle('-translate-y-2', entry.isIntersecting);
                    stickyFilterBar.classList.toggle('lg:block', !entry.isIntersecting);
                }
            }, {
                threshold: 0,
                rootMargin: '-80px 0px 0px 0px'
            }
        );

        if (heroSection) {
            headerObserver.observe(heroSection);
        }

        // =====================================================
        // STICKY FILTER BAR BEHAVIOR
        // =====================================================
        const filterChips = document.querySelectorAll('#stickyFilterBar .filter-chip');
        filterChips.forEach(chip => {
            chip.addEventListener('click', (e) => {
                e.preventDefault();
                filterChips.forEach(c => c.classList.remove('filter-chip-active'));
                chip.classList.add('filter-chip-active');
                // Add your filter logic here
                const filterType = chip.textContent.trim();
                console.log('Filter selected:', filterType);
            });
        });

        // =====================================================
        // QUICK VIEW MODAL
        // =====================================================
        function openQuickView(postId) {
            const modal = document.getElementById('quickViewModal');
            const content = document.getElementById('quickViewContent');

            // Show loading state
            content.innerHTML = `
                <div class="p-8 flex items-center justify-center min-h-[400px]">
                    <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary-500 border-t-transparent"></div>
                </div>
            `;

            modal?.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Simulate loading content (replace with actual fetch)
            setTimeout(() => {
                // Load actual content here
                content.innerHTML = `
                    <div class="grid md:grid-cols-2 gap-0">
                        <div class="aspect-[4/3] bg-secondary-200 dark:bg-secondary-700 flex items-center justify-center">
                            <span class="material-symbols-outlined text-6xl text-secondary-400">image</span>
                        </div>
                        <div class="p-6 md:p-8">
                            <span class="badge badge-primary mb-3">Nhà phố</span>
                            <h2 id="quickViewTitle" class="text-xl font-bold text-secondary-900 dark:text-white mb-4">Đang tải thông tin...</h2>
                            <div class="space-y-3 text-secondary-600 dark:text-secondary-400">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-primary-500">payments</span>
                                    <span class="font-semibold text-primary-600 dark:text-primary-400">1.5 tỷ</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-emerald-500">square_foot</span>
                                    <span>120m²</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-blue-500">hotel</span>
                                    <span>3 phòng ngủ, 2 phòng tắm</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }, 500);
        }

        function closeQuickView() {
            const modal = document.getElementById('quickViewModal');
            modal?.classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeQuickView();
            }
        });

        // =====================================================
        // TOAST NOTIFICATIONS
        // =====================================================
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');

            const icons = {
                success: 'check_circle',
                error: 'error',
                info: 'info',
                warning: 'warning'
            };

            const colors = {
                success: 'text-emerald-500',
                error: 'text-red-500',
                info: 'text-blue-500',
                warning: 'text-amber-500'
            };

            toast.className = `toast pointer-events-auto`;
            toast.innerHTML = `
                <span class="material-symbols-outlined ${colors[type]}">${icons[type]}</span>
                <span class="text-sm font-medium text-secondary-700 dark:text-secondary-300">${message}</span>
                <button onclick="this.parentElement.remove()" class="ml-2 text-secondary-400 hover:text-secondary-600 transition-colors">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            `;

            container?.appendChild(toast);

            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.style.animation = 'toastIn 0.3s ease-out reverse';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        // =====================================================
        // SAVE TO FAVORITES (localStorage)
        // =====================================================
        function toggleSave(postId, button) {
            const saved = JSON.parse(localStorage.getItem('savedPosts') || '[]');
            const index = saved.indexOf(postId);
            const icon = button.querySelector('.save-icon');
            const filledIcon = button.querySelector('.filled-icon');

            if (index === -1) {
                saved.push(postId);
                icon?.classList.add('hidden');
                filledIcon?.classList.remove('hidden');
                button.classList.add('text-red-500');
                showToast('Đã lưu tin này', 'success');
            } else {
                saved.splice(index, 1);
                icon?.classList.remove('hidden');
                filledIcon?.classList.add('hidden');
                button.classList.remove('text-red-500');
                showToast('Đã bỏ lưu tin', 'info');
            }

            localStorage.setItem('savedPosts', JSON.stringify(saved));
        }

        // =====================================================
        // SHARE FUNCTIONALITY
        // =====================================================
        function sharePost(url, title) {
            if (navigator.share) {
                navigator.share({
                    title: title,
                    url: url
                });
            } else {
                navigator.clipboard.writeText(url);
                showToast('Đã copy link chia sẻ', 'success');
            }
        }

        // =====================================================
        // SKELETON LOADING
        // =====================================================
        function showSkeletons() {
            return `
                <div class="bg-white dark:bg-dark-surface rounded-2xl overflow-hidden shadow-card">
                    <div class="skeleton aspect-[4/3]"></div>
                    <div class="p-5">
                        <div class="skeleton h-6 w-3/4 rounded-lg mb-3"></div>
                        <div class="skeleton h-4 w-full rounded-lg mb-2"></div>
                        <div class="skeleton h-4 w-2/3 rounded-lg"></div>
                    </div>
                </div>
            `;
        }

        // =====================================================
        // REDUCED MOTION CHECK
        // =====================================================
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (prefersReducedMotion) {
            document.documentElement.style.setProperty('--transition-duration', '0ms');
        }
    </script>

    @stack('scripts')
</body>

</html>
