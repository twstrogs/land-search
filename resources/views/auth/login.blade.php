<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đăng nhập - Land Search</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%230d9488' rx='20' width='100' height='100'/><path fill='white' d='M50 20L20 45v35h25V60h10v20h25V45L50 20z'/></svg>">
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght@20..48,100..700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        html.dark {
            color-scheme: dark;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex items-center justify-center p-4">
    
    <!-- Login Card -->
    <div class="w-full max-w-md">
        
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-3">
                <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-teal-500/30">
                    <span class="material-symbols-outlined text-white text-2xl">home</span>
                </div>
            </a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-4">Land Search</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Đăng nhập vào tài khoản</p>
        </div>
        
        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
            
            @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <div class="flex items-center gap-2 text-red-600 dark:text-red-400">
                    <span class="material-symbols-outlined">error</span>
                    <span class="font-medium">Đăng nhập thất bại</span>
                </div>
                <ul class="mt-2 text-sm text-red-600 dark:text-red-400 list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">mail</span>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full pl-11 pr-4 py-3 bg-gray-100 dark:bg-gray-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 @error('email') ring-2 ring-red-500 @enderror"
                               placeholder="email@example.com">
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Mật khẩu</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">lock</span>
                        <input type="password" id="password" name="password" required
                               class="w-full pl-11 pr-4 py-3 bg-gray-100 dark:bg-gray-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 @error('password') ring-2 ring-red-500 @enderror"
                               placeholder="••••••••">
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-teal-600 bg-gray-100 dark:bg-gray-700 border-0 rounded focus:ring-2 focus:ring-teal-500">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Ghi nhớ đăng nhập</span>
                    </label>
                </div>
                
                <button type="submit" class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2 shadow-lg shadow-teal-500/30">
                    <span class="material-symbols-outlined">login</span>
                    Đăng nhập
                </button>
            </form>
        </div>
        
        <!-- Register Link -->
        {{-- <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-6">
            Chưa có tài khoản? 
            <a href="{{ route('register') }}" class="text-teal-600 hover:text-teal-700 font-medium">Đăng ký</a>
        </p> --}}
    </div>
    
</body>
</html>
