@extends('layouts.app')

@section('title', 'Land Search - Tìm kiếm Bất động sản Thái Nguyên')

@section('content')
<!-- =====================================================
     HERO SECTION
===================================================== -->
<section class="relative pt-24 pb-16 lg:pt-32 lg:pb-24 overflow-hidden">
    <!-- Background -->
    <div class="absolute inset-0 -z-10">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-teal-50/30 to-slate-50 dark:from-slate-900 dark:via-slate-900 dark:to-slate-900"></div>
        <div class="absolute top-20 right-0 w-[500px] h-[500px] bg-teal-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-teal-400/10 rounded-full blur-3xl"></div>
    </div>
    
    <div class="container mx-auto px-4 lg:px-6">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <!-- Left: Content -->
            <div class="text-center lg:text-left">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-teal-50 dark:bg-teal-900/30 rounded-full text-teal-700 dark:text-teal-300 text-sm font-medium mb-6">
                    <span class="material-symbols-outlined text-base">auto_awesome</span>
                    <span>Hơn 10,000+ bất động sản</span>
                </div>
                
                <!-- Headline -->
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-slate-900 dark:text-white leading-tight mb-6">
                    Tìm ngôi nhà
                    <span class="bg-gradient-to-r from-teal-500 to-teal-400 bg-clip-text text-transparent"> hoàn hảo</span>
                    <br>tại Thái Nguyên
                </h1>
                
                <!-- Subtitle -->
                <p class="text-lg lg:text-xl text-slate-600 dark:text-slate-400 mb-8 max-w-xl mx-auto lg:mx-0">
                    Nền tảng tìm kiếm bất động sản thông minh với hàng nghìn tin rao được cập nhật liên tục. Tìm kiếm nhanh chóng, miễn phí.
                </p>
                
                <!-- Quick Stats -->
                <div class="flex flex-wrap justify-center lg:justify-start gap-8 mb-8">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-teal-600 dark:text-teal-400">10K+</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Tin đăng</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-teal-600 dark:text-teal-400">5</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Quận/Huyện</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-teal-600 dark:text-teal-400">24/7</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Cập nhật</p>
                    </div>
                </div>
            </div>
            
            <!-- Right: Search Box -->
            <div class="relative">
                <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl p-6 lg:p-8 border border-slate-200 dark:border-slate-700">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-teal-500">search</span>
                        Tìm kiếm bất động sản
                    </h2>
                    
                    <form action="{{ route('search') }}" method="GET" class="space-y-4">
                        <!-- Keyword -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Từ khóa</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                                <input type="text" name="keyword" value="{{ request('keyword') }}"
                                    class="w-full pl-12 pr-4 py-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                                    placeholder="VD: đất nền, nhà phố, căn hộ...">
                            </div>
                        </div>
                        
                        <!-- Location -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Khu vực</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">location_on</span>
                                <select name="location" class="w-full pl-12 pr-4 py-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all appearance-none">
                                    <option value="">Toàn bộ Thái Nguyên</option>
                                    <option value="TP Thái Nguyên">TP. Thái Nguyên</option>
                                    <option value="Sông Công">TX. Sông Công</option>
                                    <option value="Đại Từ">H. Đại Từ</option>
                                    <option value="Phổ Yên">H. Phổ Yên</option>
                                    <option value="Định Hóa">H. Định Hóa</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">expand_more</span>
                            </div>
                        </div>
                        
                        <!-- Property Type & Price Row -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Loại BĐS</label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">home</span>
                                    <select name="property_type" class="w-full pl-12 pr-8 py-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all appearance-none">
                                        <option value="">Tất cả</option>
                                        <option value="Đất nền/Đất thổ cư">Đất nền</option>
                                        <option value="Nhà phố/Nhà riêng">Nhà phố</option>
                                        <option value="Căn hộ chung cư">Chung cư</option>
                                        <option value="Biệt thự">Biệt thự</option>
                                        <option value="Đất nông nghiệp">Đất nông nghiệp</option>
                                    </select>
                                    <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">expand_more</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Mức giá</label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">payments</span>
                                    <select name="price_range" class="w-full pl-12 pr-8 py-4 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all appearance-none">
                                        <option value="">Tất cả</option>
                                        <option value="0-1000">Dưới 1 tỷ</option>
                                        <option value="1000-2000">1 - 2 tỷ</option>
                                        <option value="2000-5000">2 - 5 tỷ</option>
                                        <option value="5000-10000">5 - 10 tỷ</option>
                                        <option value="10000-0">Trên 10 tỷ</option>
                                    </select>
                                    <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">expand_more</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit -->
                        <button type="submit" class="w-full py-4 bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">search</span>
                            <span>Tìm kiếm ngay</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     PROPERTY TYPES
===================================================== -->
<section class="py-16 lg:py-20">
    <div class="container mx-auto px-4 lg:px-6">
        <!-- Section Header -->
        <div class="text-center mb-12">
            <span class="inline-block px-4 py-1.5 bg-teal-50 dark:bg-teal-900/30 rounded-full text-teal-600 dark:text-teal-400 text-sm font-medium mb-4">Danh mục</span>
            <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white mb-4">Khám phá loại bất động sản</h2>
            <p class="text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">Tìm kiếm theo nhu cầu của bạn với các loại bất động sản đa dạng</p>
        </div>
        
        <!-- Property Type Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 lg:gap-6">
            <a href="{{ route('search', ['property_type' => 'Đất nền/Đất thổ cư']) }}" class="group bg-white dark:bg-slate-800 rounded-2xl p-6 text-center border border-slate-200 dark:border-slate-700 hover:border-teal-300 dark:hover:border-teal-600 hover:shadow-xl hover:shadow-teal-500/10 transition-all hover:-translate-y-1">
                <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-3xl text-white">terrain</span>
                </div>
                <h3 class="font-semibold text-slate-900 dark:text-white mb-1">Đất nền</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">2,450 tin</p>
            </a>
            
            <a href="{{ route('search', ['property_type' => 'Nhà phố/Nhà riêng']) }}" class="group bg-white dark:bg-slate-800 rounded-2xl p-6 text-center border border-slate-200 dark:border-slate-700 hover:border-teal-300 dark:hover:border-teal-600 hover:shadow-xl hover:shadow-teal-500/10 transition-all hover:-translate-y-1">
                <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-3xl text-white">house</span>
                </div>
                <h3 class="font-semibold text-slate-900 dark:text-white mb-1">Nhà phố</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">1,820 tin</p>
            </a>
            
            <a href="{{ route('search', ['property_type' => 'Căn hộ chung cư']) }}" class="group bg-white dark:bg-slate-800 rounded-2xl p-6 text-center border border-slate-200 dark:border-slate-700 hover:border-teal-300 dark:hover:border-teal-600 hover:shadow-xl hover:shadow-teal-500/10 transition-all hover:-translate-y-1">
                <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-purple-400 to-purple-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-3xl text-white">apartment</span>
                </div>
                <h3 class="font-semibold text-slate-900 dark:text-white mb-1">Chung cư</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">890 tin</p>
            </a>
            
            <a href="{{ route('search', ['property_type' => 'Biệt thự']) }}" class="group bg-white dark:bg-slate-800 rounded-2xl p-6 text-center border border-slate-200 dark:border-slate-700 hover:border-teal-300 dark:hover:border-teal-600 hover:shadow-xl hover:shadow-teal-500/10 transition-all hover:-translate-y-1">
                <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-amber-400 to-amber-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-3xl text-white">villa</span>
                </div>
                <h3 class="font-semibold text-slate-900 dark:text-white mb-1">Biệt thự</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">320 tin</p>
            </a>
            
            <a href="{{ route('search', ['property_type' => 'Đất nông nghiệp']) }}" class="group bg-white dark:bg-slate-800 rounded-2xl p-6 text-center border border-slate-200 dark:border-slate-700 hover:border-teal-300 dark:hover:border-teal-600 hover:shadow-xl hover:shadow-teal-500/10 transition-all hover:-translate-y-1">
                <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-lime-400 to-lime-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-3xl text-white">grass</span>
                </div>
                <h3 class="font-semibold text-slate-900 dark:text-white mb-1">Đất nông nghiệp</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">650 tin</p>
            </a>
        </div>
    </div>
</section>

<!-- =====================================================
     FEATURED PROPERTIES
===================================================== -->
@if(isset($featuredPosts) && $featuredPosts->count() > 0)
<section class="py-16 lg:py-20 bg-slate-50 dark:bg-slate-900/50">
    <div class="container mx-auto px-4 lg:px-6">
        <!-- Section Header -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-12">
            <div>
                <span class="inline-block px-4 py-1.5 bg-teal-50 dark:bg-teal-900/30 rounded-full text-teal-600 dark:text-teal-400 text-sm font-medium mb-4">Nổi bật</span>
                <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white mb-2">Bất động sản tiêu biểu</h2>
                <p class="text-slate-600 dark:text-slate-400">Những tin rao được quan tâm nhiều nhất</p>
            </div>
            <a href="{{ route('search', ['sort' => 'views']) }}" class="inline-flex items-center gap-2 text-teal-600 dark:text-teal-400 font-semibold hover:text-teal-700 dark:hover:text-teal-300 transition-colors">
                <span>Xem tất cả</span>
                <span class="material-symbols-outlined">arrow_forward</span>
            </a>
        </div>
        
        <!-- Property Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($featuredPosts->take(6) as $index => $post)
                @include('public.components.property-card', ['post' => $post, 'index' => $index])
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- =====================================================
     LOCATIONS
===================================================== -->
<section class="py-16 lg:py-20">
    <div class="container mx-auto px-4 lg:px-6">
        <!-- Section Header -->
        <div class="text-center mb-12">
            <span class="inline-block px-4 py-1.5 bg-teal-50 dark:bg-teal-900/30 rounded-full text-teal-600 dark:text-teal-400 text-sm font-medium mb-4">Khu vực</span>
            <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white mb-4">Khám phá theo địa điểm</h2>
            <p class="text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">Tìm bất động sản tại các quận huyện trong Thái Nguyên</p>
        </div>
        
        <!-- Location Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
            <a href="{{ route('search', ['location' => 'TP Thái Nguyên']) }}" class="group relative bg-gradient-to-br from-teal-500 to-teal-600 rounded-2xl p-6 text-white overflow-hidden">
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
                <div class="relative">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-2xl">location_city</span>
                    </div>
                    <h3 class="text-xl font-bold mb-1">TP. Thái Nguyên</h3>
                    <p class="text-white/80 text-sm">3,240 bất động sản</p>
                </div>
            </a>
            
            <a href="{{ route('search', ['location' => 'Phổ Yên']) }}" class="group relative bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white overflow-hidden">
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
                <div class="relative">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-2xl">factory</span>
                    </div>
                    <h3 class="text-xl font-bold mb-1">H. Phổ Yên</h3>
                    <p class="text-white/80 text-sm">2,150 bất động sản</p>
                </div>
            </a>
            
            <a href="{{ route('search', ['location' => 'Sông Công']) }}" class="group relative bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white overflow-hidden">
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
                <div class="relative">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-2xl">home</span>
                    </div>
                    <h3 class="text-xl font-bold mb-1">TX. Sông Công</h3>
                    <p class="text-white/80 text-sm">1,420 bất động sản</p>
                </div>
            </a>
            
            <a href="{{ route('search', ['location' => 'Đại Từ']) }}" class="group relative bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-6 text-white overflow-hidden">
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
                <div class="relative">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-2xl">landscape</span>
                    </div>
                    <h3 class="text-xl font-bold mb-1">H. Đại Từ</h3>
                    <p class="text-white/80 text-sm">980 bất động sản</p>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- =====================================================
     WHY CHOOSE US
===================================================== -->
<section class="py-16 lg:py-20 bg-slate-900 dark:bg-slate-950 text-white">
    <div class="container mx-auto px-4 lg:px-6">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <!-- Left: Content -->
            <div>
                <span class="inline-block px-4 py-1.5 bg-teal-500/20 rounded-full text-teal-400 text-sm font-medium mb-4">Tại sao chọn chúng tôi</span>
                <h2 class="text-3xl lg:text-4xl font-bold mb-6">Nền tảng tìm kiếm BĐS thông minh nhất Thái Nguyên</h2>
                <p class="text-slate-400 mb-8">Chúng tôi kết hợp công nghệ AI tiên tiến với dữ liệu phong phú, giúp bạn tìm được bất động sản phù hợp một cách nhanh chóng và dễ dàng.</p>
                
                <div class="grid grid-cols-2 gap-6">
                    <div class="bg-slate-800/50 rounded-2xl p-5">
                        <div class="w-12 h-12 bg-teal-500/20 rounded-xl flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-2xl text-teal-400">speed</span>
                        </div>
                        <h3 class="font-semibold mb-2">Tìm kiếm nhanh</h3>
                        <p class="text-sm text-slate-400">Kết quả tức thì với công nghệ tìm kiếm thông minh</p>
                    </div>
                    <div class="bg-slate-800/50 rounded-2xl p-5">
                        <div class="w-12 h-12 bg-teal-500/20 rounded-xl flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-2xl text-teal-400">verified</span>
                        </div>
                        <h3 class="font-semibold mb-2">Dữ liệu thật</h3>
                        <p class="text-sm text-slate-400">Tin rao được kiểm duyệt và cập nhật liên tục</p>
                    </div>
                    <div class="bg-slate-800/50 rounded-2xl p-5">
                        <div class="w-12 h-12 bg-teal-500/20 rounded-xl flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-2xl text-teal-400">psychology</span>
                        </div>
                        <h3 class="font-semibold mb-2">AI thông minh</h3>
                        <p class="text-sm text-slate-400">Tìm kiếm bằng ngôn ngữ tự nhiên</p>
                    </div>
                    <div class="bg-slate-800/50 rounded-2xl p-5">
                        <div class="w-12 h-12 bg-teal-500/20 rounded-xl flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-2xl text-teal-400">attach_money</span>
                        </div>
                        <h3 class="font-semibold mb-2">Miễn phí</h3>
                        <p class="text-sm text-slate-400">Sử dụng hoàn toàn miễn phí, không phí dịch vụ</p>
                    </div>
                </div>
            </div>
            
            <!-- Right: Image -->
            <div class="relative">
                <div class="aspect-square rounded-3xl overflow-hidden">
                    <div class="w-full h-full bg-gradient-to-br from-teal-500/20 to-blue-500/20 flex items-center justify-center">
                        <div class="text-center">
                            <span class="material-symbols-outlined text-[120px] text-teal-400/50">home</span>
                        </div>
                    </div>
                </div>
                <!-- Floating Card -->
                <div class="absolute -bottom-6 -left-6 bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-2xl max-w-[280px]">
                    <div class="flex items-center gap-4 mb-3">
                        <div class="w-12 h-12 bg-teal-100 dark:bg-teal-900/30 rounded-xl flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl text-teal-600 dark:text-teal-400">trending_up</span>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900 dark:text-white">+25%</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Tăng trưởng tháng</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     CTA SECTION
===================================================== -->
<section class="py-16 lg:py-20">
    <div class="container mx-auto px-4 lg:px-6">
        <div class="bg-gradient-to-r from-teal-500 to-teal-600 rounded-3xl p-8 lg:p-12 text-center text-white relative overflow-hidden">
            <!-- Decorative -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
            
            <div class="relative">
                <h2 class="text-3xl lg:text-4xl font-bold mb-4">Sẵn sàng tìm ngôi nhà mơ ước?</h2>
                <p class="text-lg text-white/80 mb-8 max-w-2xl mx-auto">Đăng ký nhận thông báo tin rao mới nhất phù hợp với nhu cầu của bạn</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('search') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-teal-600 font-bold rounded-xl hover:bg-slate-100 transition-colors shadow-lg">
                        <span class="material-symbols-outlined">search</span>
                        <span>Bắt đầu tìm kiếm</span>
                    </a>
                    <a href="#" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-transparent border-2 border-white text-white font-bold rounded-xl hover:bg-white/10 transition-colors">
                        <span class="material-symbols-outlined">notifications</span>
                        <span>Nhận thông báo</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
