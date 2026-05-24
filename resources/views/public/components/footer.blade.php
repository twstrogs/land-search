{{-- Public Footer Component - Modern Real Estate Style --}}
<footer class="bg-slate-900 dark:bg-slate-950 text-white pt-16 pb-8" role="contentinfo">
    <div class="container mx-auto px-4 lg:px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12 mb-12">
            
            <!-- Brand -->
            <div class="lg:col-span-1">
                <a href="{{ route('home') }}" class="flex items-center gap-3 mb-4">
                    <div class="w-11 h-11 bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg shadow-teal-500/30">
                        <span class="material-symbols-outlined text-white text-xl">home</span>
                    </div>
                    <div>
                        <span class="text-xl font-bold">Land Search</span>
                        <span class="block text-xs text-slate-400">Bất động sản Thái Nguyên</span>
                    </div>
                </a>
                <p class="text-sm text-slate-400 leading-relaxed mb-6">
                    Nền tảng tra cứu bất động sản thông minh tại Thái Nguyên. Tìm kiếm nhanh chóng, miễn phí.
                </p>
                <!-- Social Links -->
                <div class="flex items-center gap-3">
                    <a href="#" class="w-10 h-10 rounded-xl bg-slate-800 hover:bg-teal-500 flex items-center justify-center text-slate-400 hover:text-white transition-all" aria-label="Facebook">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-xl bg-slate-800 hover:bg-red-500 flex items-center justify-center text-slate-400 hover:text-white transition-all" aria-label="YouTube">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-xl bg-slate-800 hover:bg-blue-500 flex items-center justify-center text-slate-400 hover:text-white transition-all" aria-label="Zalo">
                        <svg class="w-5 h-5" viewBox="0 0 48 48" fill="none">
                            <path fill="#0068FF" d="M24 4C12.954 4 4 12.954 4 24s8.954 20 20 20 20-8.954 20-20S35.046 4 24 4z"/>
                            <path fill="#fff" d="M24 9c1.5 0 2.7.4 3.6 1.2l-1.2 1.1c-.5-.4-1.2-.6-2-.6-1.8 0-3 1.2-3 3s1.2 3 3 3c.8 0 1.5-.2 2-.6l1.2 1.1c-.9.8-2.1 1.2-3.6 1.2-3 0-5-2-5-5s2-5 5-5z"/>
                        </svg>
                    </a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h4 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-teal-400">search</span>
                    Tra cứu nhanh
                </h4>
                <ul class="space-y-3">
                    <li><a href="{{ route('search', ['property_type' => 'Đất nền/Đất thổ cư']) }}" class="text-sm text-slate-400 hover:text-teal-400 transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-xs">chevron_right</span> Đất nền</a></li>
                    <li><a href="{{ route('search', ['property_type' => 'Nhà phố/Nhà riêng']) }}" class="text-sm text-slate-400 hover:text-teal-400 transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-xs">chevron_right</span> Nhà phố</a></li>
                    <li><a href="{{ route('search', ['property_type' => 'Căn hộ chung cư']) }}" class="text-sm text-slate-400 hover:text-teal-400 transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-xs">chevron_right</span> Chung cư</a></li>
                    <li><a href="{{ route('search', ['price_max' => 1000000000]) }}" class="text-sm text-slate-400 hover:text-teal-400 transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-xs">chevron_right</span> Dưới 1 tỷ</a></li>
                </ul>
            </div>
            
            <!-- Districts -->
            <div>
                <h4 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-teal-400">location_on</span>
                    Quận/Huyện
                </h4>
                <ul class="space-y-3">
                    <li><a href="{{ route('search', ['location' => 'TP Thái Nguyên']) }}" class="text-sm text-slate-400 hover:text-teal-400 transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-xs">chevron_right</span> TP. Thái Nguyên</a></li>
                    <li><a href="{{ route('search', ['location' => 'Sông Công']) }}" class="text-sm text-slate-400 hover:text-teal-400 transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-xs">chevron_right</span> TX. Sông Công</a></li>
                    <li><a href="{{ route('search', ['location' => 'Đại Từ']) }}" class="text-sm text-slate-400 hover:text-teal-400 transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-xs">chevron_right</span> H. Đại Từ</a></li>
                    <li><a href="{{ route('search', ['location' => 'Phổ Yên']) }}" class="text-sm text-slate-400 hover:text-teal-400 transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-xs">chevron_right</span> H. Phổ Yên</a></li>
                </ul>
            </div>
            
            <!-- Contact -->
            <div>
                <h4 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-teal-400">contact_phone</span>
                    Liên hệ
                </h4>
                <ul class="space-y-3">
                    <li class="flex items-center gap-3 text-sm text-slate-400">
                        <span class="material-symbols-outlined text-teal-400">email</span>
                        <a href="mailto:contact@landsearch.vn" class="hover:text-teal-400 transition-colors">contact@landsearch.vn</a>
                    </li>
                    <li class="flex items-center gap-3 text-sm text-slate-400">
                        <span class="material-symbols-outlined text-teal-400">phone</span>
                        <a href="tel:0123456789" class="hover:text-teal-400 transition-colors">0123 456 789</a>
                    </li>
                    <li class="flex items-start gap-3 text-sm text-slate-400">
                        <span class="material-symbols-outlined text-teal-400">location_on</span>
                        <span>Thái Nguyên, Việt Nam</span>
                    </li>
                </ul>
                
                <!-- Newsletter -->
                <div class="mt-6">
                    <p class="text-sm text-slate-400 mb-3">Đăng ký nhận tin mới:</p>
                    <form class="flex gap-2">
                        <input type="email" placeholder="Email của bạn" class="flex-1 px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-teal-500 transition-colors">
                        <button type="submit" class="px-4 py-2.5 bg-teal-500 hover:bg-teal-600 text-white rounded-xl transition-colors">
                            <span class="material-symbols-outlined">send</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Bottom -->
        <div class="pt-8 border-t border-slate-800">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-center md:text-left">
                    <p class="text-sm text-slate-500">
                        © {{ date('Y') }} Land Search. Mọi quyền được bảo lưu.
                    </p>
                    <p class="text-xs text-slate-600 mt-1">
                        Nền tảng tìm kiếm bất động sản thông minh
                    </p>
                </div>
                <div class="flex items-center gap-6">
                    <a href="#" class="text-sm text-slate-500 hover:text-teal-400 transition-colors">Điều khoản</a>
                    <a href="#" class="text-sm text-slate-500 hover:text-teal-400 transition-colors">Bảo mật</a>
                    <a href="#" class="text-sm text-slate-500 hover:text-teal-400 transition-colors">Liên hệ</a>
                </div>
            </div>
        </div>
    </div>
</footer>
