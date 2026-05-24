{{-- Public Header Component - Modern Real Estate Style --}}
<header id="publicHeader" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
    <nav class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-slate-200/50 dark:border-slate-700/50" role="navigation" aria-label="Main navigation">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="flex items-center justify-between h-16 lg:h-[72px]">
                
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 group" aria-label="Land Search - Trang chủ">
                    <div class="relative w-11 h-11">
                        <div class="absolute inset-0 bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl rotate-3 group-hover:rotate-6 transition-transform duration-300"></div>
                        <div class="relative w-full h-full bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg shadow-teal-500/30 group-hover:shadow-teal-500/50 transition-shadow">
                            <span class="material-symbols-outlined text-white text-xl">home</span>
                        </div>
                    </div>
                    <div class="hidden sm:block">
                        <span class="text-xl font-bold bg-gradient-to-r from-teal-600 to-teal-500 bg-clip-text text-transparent">Land Search</span>
                        <span class="hidden lg:block text-xs text-slate-500 dark:text-slate-400 -mt-0.5">Bất động sản Thái Nguyên</span>
                    </div>
                </a>
                
                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center gap-1">
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">home</span>
                            <span>Trang chủ</span>
                        </span>
                    </a>
                    <a href="{{ route('search') }}" class="nav-link {{ request()->routeIs('search') ? 'active' : '' }}">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">search</span>
                            <span>Tìm kiếm</span>
                        </span>
                    </a>
                    <a href="#" class="nav-link">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">map</span>
                            <span>Bản đồ</span>
                        </span>
                    </a>
                </div>
                
                <!-- Actions -->
                <div class="flex items-center gap-2 lg:gap-3">
                    <!-- Search Toggle (Mobile) -->
                    <a href="{{ route('search') }}" class="lg:hidden p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors" aria-label="Tìm kiếm">
                        <span class="material-symbols-outlined">search</span>
                    </a>
                    
                    <!-- Theme Toggle -->
                    <button id="themeToggle" class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all hover:scale-105" aria-label="Chuyển đổi giao diện">
                        <span class="material-symbols-outlined sun-icon">light_mode</span>
                        <span class="material-symbols-outlined moon-icon hidden">dark_mode</span>
                    </button>
                    
                    <!-- Auth Buttons -->
                    <div class="hidden sm:flex items-center gap-2">
                        @auth
                            <a href="{{ route('dashboard.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40">
                                <span class="material-symbols-outlined text-lg">dashboard</span>
                                <span>Dashboard</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 border-2 border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:border-teal-500 hover:text-teal-600 font-semibold rounded-xl transition-all">
                                <span>Đăng nhập</span>
                            </a>
                        @endauth
                    </div>
                    
                    <!-- Mobile Menu Toggle -->
                    <button id="mobileMenuToggle" class="lg:hidden p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors" aria-label="Menu" aria-expanded="false">
                        <span class="material-symbols-outlined menu-icon">menu</span>
                        <span class="material-symbols-outlined close-icon hidden">close</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Mobile Menu Drawer -->
    <div id="mobileMenu" class="lg:hidden hidden fixed inset-0 top-16 bg-white dark:bg-slate-900 z-40" role="dialog" aria-modal="true">
        <div class="container mx-auto px-4 py-6 space-y-3">
            <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('home') ? 'bg-teal-50 dark:bg-teal-900/20 text-teal-600 dark:text-teal-400 font-semibold' : 'text-slate-700 dark:text-slate-300 font-medium' }}">
                <span class="material-symbols-outlined">home</span>
                <span>Trang chủ</span>
            </a>
            <a href="{{ route('search') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('search') ? 'bg-teal-50 dark:bg-teal-900/20 text-teal-600 dark:text-teal-400 font-semibold' : 'text-slate-700 dark:text-slate-300 font-medium' }}">
                <span class="material-symbols-outlined">search</span>
                <span>Tìm kiếm BĐS</span>
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 dark:text-slate-300 font-medium">
                <span class="material-symbols-outlined">map</span>
                <span>Xem bản đồ</span>
            </a>
            <hr class="border-slate-200 dark:border-slate-700 my-4">
            @guest
            <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 dark:text-slate-300 font-medium">
                <span class="material-symbols-outlined">login</span>
                <span>Đăng nhập</span>
            </a>
            @else
            <a href="{{ route('dashboard.index') }}" class="w-full py-3 bg-gradient-to-r from-teal-500 to-teal-600 text-white font-semibold rounded-xl text-center shadow-lg shadow-teal-500/30 flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">dashboard</span>
                <span>Dashboard</span>
            </a>
            @endguest
        </div>
    </div>
</header>

@push('scripts')
<script>
    // Theme toggle
    const themeToggle = document.getElementById('themeToggle');
    const html = document.documentElement;
    const sunIcon = themeToggle?.querySelector('.sun-icon');
    const moonIcon = themeToggle?.querySelector('.moon-icon');
    
    // Initialize icon state
    function updateThemeIcon() {
        if (html.classList.contains('dark')) {
            sunIcon?.classList.add('hidden');
            moonIcon?.classList.remove('hidden');
        } else {
            sunIcon?.classList.remove('hidden');
            moonIcon?.classList.add('hidden');
        }
    }
    updateThemeIcon();
    
    themeToggle?.addEventListener('click', () => {
        html.classList.toggle('dark');
        localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        updateThemeIcon();
    });
    
    // Mobile menu
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
    
    // Sticky header shadow on scroll
    const header = document.getElementById('publicHeader');
    let lastScroll = 0;
    
    window.addEventListener('scroll', () => {
        const currentScroll = window.scrollY;
        if (currentScroll > 10) {
            header?.classList.add('shadow-lg');
        } else {
            header?.classList.remove('shadow-lg');
        }
        lastScroll = currentScroll;
    }, { passive: true });
</script>
@endpush
