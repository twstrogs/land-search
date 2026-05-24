<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Land Search - Tìm kiếm Bất động sản Thái Nguyên')</title>
    <meta name="description" content="Tìm kiếm nhà đất, đất nền, chung cư tại Thái Nguyên. Hàng nghìn tin rao được cập nhật liên tục. Tra cứu nhanh chóng, miễn phí.">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%230d9488' rx='20' width='100' height='100'/><path fill='white' d='M50 20L20 45v35h25V60h10v20h25V45L50 20z'/></svg>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght@20..48,100..700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    @stack('styles')
    @stack('schema')
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 antialiased transition-colors duration-300">
    
    <!-- Skip to content -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2 focus:bg-teal-600 focus:text-white focus:rounded-lg">
        Skip to main content
    </a>

    <!-- Header -->
    @hasSection('header')
        @yield('header')
    @else
        @include('public.components.header')
    @endif

    <!-- Main Content -->
    <main id="main-content" role="main">
        @yield('content')
    </main>

    <!-- Footer -->
    @hasSection('footer')
        @yield('footer')
    @else
        @include('public.components.footer')
    @endif

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed bottom-6 right-6 z-[100] flex flex-col gap-3 pointer-events-none"></div>

    <!-- Theme Script (inline to prevent flash) - Default: Light Mode -->
    <script>
        (function() {
            // Always remove dark class first to ensure light mode default
            document.documentElement.classList.remove('dark');
            
            // Only add dark mode if explicitly saved in localStorage
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    
    @stack('scripts')
</body>
</html>
