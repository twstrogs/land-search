@extends('layouts.dashboard')

@section('title', 'Tạo tin đăng')

@section('breadcrumb')
    <a href="{{ route('dashboard.posts.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">Tin đăng</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-700 dark:text-gray-300">Tạo mới</span>
@endsection

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tạo tin đăng mới</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Điền thông tin bất động sản</p>
</div>

<form action="{{ route('dashboard.posts.store') }}" method="POST" class="space-y-6">
    @csrf
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Basic Info -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Thông tin cơ bản</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tiêu đề <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                               class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 @error('title') ring-2 ring-red-500 @enderror"
                               placeholder="VD: Bán nhà phố Quận 7, 80m2, 3 tầng">
                        @error('title')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Mô tả</label>
                        <textarea name="description" rows="5"
                                  class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 @error('description') ring-2 ring-red-500 @enderror"
                                  placeholder="Mô tả chi tiết về bất động sản...">{{ old('description') }}</textarea>
                        @error('description')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nội dung gốc (raw)</label>
                        <textarea name="raw_content" rows="3"
                                  class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 font-mono text-xs"
                                  placeholder="Nội dung gốc từ nguồn...">{{ old('raw_content') }}</textarea>
                    </div>
                </div>
            </div>
            
            <!-- Price & Area -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Giá & Diện tích</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Giá (text)</label>
                        <input type="text" name="price_text" value="{{ old('price_text') }}"
                               class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500"
                               placeholder="VD: 3 tỷ 500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Giá (VNĐ)</label>
                        <input type="number" name="price_value" value="{{ old('price_value') }}"
                               class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500"
                               placeholder="3500000000">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Diện tích (text)</label>
                        <input type="text" name="area_text" value="{{ old('area_text') }}"
                               class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500"
                               placeholder="VD: 80m2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Diện tích (m²)</label>
                        <input type="number" step="0.01" name="area_value" value="{{ old('area_value') }}"
                               class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500"
                               placeholder="80">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Mặt tiền (m)</label>
                        <input type="text" name="frontage_texts" value="{{ old('frontage_texts') }}"
                               class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500"
                               placeholder="VD: 5m">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Chiều sâu (m)</label>
                        <input type="text" name="depth_text" value="{{ old('depth_text') }}"
                               class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500"
                               placeholder="VD: 20m">
                    </div>
                </div>
            </div>
            
            <!-- Location -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Địa chỉ</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Địa chỉ đầy đủ</label>
                        <input type="text" name="address_text" value="{{ old('address_text') }}"
                               class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500"
                               placeholder="VD: 123 Nguyễn Trãi, Phường 2">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Phường/Xã</label>
                            <input type="text" name="ward" value="{{ old('ward') }}"
                                   class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500"
                                   placeholder="Phường 2">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Quận/Huyện</label>
                            <input type="text" name="district" value="{{ old('district') }}"
                                   class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500"
                                   placeholder="Quận 7">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tỉnh/TP</label>
                            <input type="text" name="city" value="{{ old('city') }}"
                                   class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500"
                                   placeholder="Hồ Chí Minh">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Publish -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Xuất bản</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Loại BĐS</label>
                        <select name="property_type" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm">
                            <option value="">Chọn loại...</option>
                            <option value="Nhà phố/Nhà riêng" {{ old('property_type') == 'Nhà phố/Nhà riêng' ? 'selected' : '' }}>Nhà phố/Nhà riêng</option>
                            <option value="Căn hộ chung cư" {{ old('property_type') == 'Căn hộ chung cư' ? 'selected' : '' }}>Căn hộ chung cư</option>
                            <option value="Biệt thự (Villa)" {{ old('property_type') == 'Biệt thự (Villa)' ? 'selected' : '' }}>Biệt thự</option>
                            <option value="Đất nền/Đất thổ cư" {{ old('property_type') == 'Đất nền/Đất thổ cư' ? 'selected' : '' }}>Đất nền</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="w-full px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Đăng tin
                    </button>
                </div>
            </div>
            
            <!-- Images -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Hình ảnh</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">URL Ảnh chính</label>
                        <input type="url" name="image_url" value="{{ old('image_url') }}"
                               class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500"
                               placeholder="https://...">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Facebook URL</label>
                        <input type="url" name="facebook_url" value="{{ old('facebook_url') }}"
                               class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500"
                               placeholder="https://facebook.com/...">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tác giả</label>
                        <input type="text" name="author_name" value="{{ old('author_name') }}"
                               class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-teal-500"
                               placeholder="Nguyễn Văn A">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
