@extends('layouts.dashboard')

@section('title', 'Cài đặt')

@section('breadcrumb')
    <span class="text-gray-700 dark:text-gray-300">Cài đặt</span>
@endsection

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Cài đặt</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Quản lý cấu hình hệ thống</p>
</div>

<form action="{{ route('dashboard.settings.update') }}" method="POST">
    @csrf
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Settings -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- AI Settings -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-colors duration-300">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">AI Provider</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Cấu hình dịch vụ AI</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Provider</label>
                        <select name="settings[ai.provider]" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm text-gray-900 dark:text-white transition-colors duration-300">
                            <option value="ollama" {{ ($aiSettings['provider'] ?? '') === 'ollama' ? 'selected' : '' }}>Ollama</option>
                            <option value="gemini" {{ ($aiSettings['provider'] ?? '') === 'gemini' ? 'selected' : '' }}>Google Gemini</option>
                            <option value="openai" {{ ($aiSettings['provider'] ?? '') === 'openai' ? 'selected' : '' }}>OpenAI</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Ollama URL</label>
                            <input type="url" name="settings[ai.ollama_url]" value="{{ $aiSettings['ollama_url'] ?? 'https://ollama.com' }}"
                                   class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-colors duration-300"
                                   placeholder="https://ollama.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Ollama Model</label>
                            <select name="settings[ai.ollama_model]" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm text-gray-900 dark:text-white transition-colors duration-300">
                            <option value="gpt-oss:120b-cloud" {{ ($aiSettings['ollama_model'] ?? '') === 'gpt-oss:120b-cloud' ? 'selected' : '' }}>GPT-OSS 120B Cloud</option>
                            <!-- <option value="llama3.2" {{ ($aiSettings['ollama_model'] ?? '') === 'llama3.2' ? 'selected' : '' }}>Llama 3.2</option>
                            <option value="llama3.1" {{ ($aiSettings['ollama_model'] ?? '') === 'llama3.1' ? 'selected' : '' }}>Llama 3.1</option>
                            <option value="mistral" {{ ($aiSettings['ollama_model'] ?? '') === 'mistral' ? 'selected' : '' }}>Mistral</option>
                            <option value="codellama" {{ ($aiSettings['ollama_model'] ?? '') === 'codellama' ? 'selected' : '' }}>Code Llama</option> -->
                        </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Ollama API Key</label>
                            <input type="password" name="settings[ai.ollama_api_key]" value="{{ $aiSettings['ollama_api_key'] ?? '' }}"
                                   class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-colors duration-300"
                                   placeholder="ollama_...">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Gemini API Key</label>
                        <input type="password" name="settings[ai.gemini_api_key]" value="{{ $aiSettings['gemini_api_key'] ?? '' }}"
                               class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-colors duration-300"
                               placeholder="AIza...">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Gemini Model</label>
                        <input type="text" name="settings[ai.gemini_model]" value="{{ $aiSettings['gemini_model'] ?? 'gemini-2.0-flash' }}"
                               class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-colors duration-300"
                               placeholder="gemini-2.0-flash">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">OpenAI API Key</label>
                        <input type="password" name="settings[ai.openai_api_key]" value="{{ $aiSettings['openai_api_key'] ?? '' }}"
                               class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-colors duration-300"
                               placeholder="sk-...">
                    </div>
                </div>
            </div>
            
            <!-- Scraper Settings -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-colors duration-300">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Scraper</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Cấu hình scrape Facebook</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="settings[scraper.auto_enabled]" id="auto_enabled" value="1" 
                               {{ ($scraperSettings['auto_enabled'] ?? false) ? 'checked' : '' }}
                               class="w-4 h-4 text-teal-600 bg-gray-100 dark:bg-gray-700 border-0 rounded focus:ring-2 focus:ring-teal-500 transition-colors duration-300">
                        <label for="auto_enabled" class="text-sm font-medium text-gray-700 dark:text-gray-300">Bật auto-scrape</label>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Thời gian chạy</label>
                            <input type="time" name="settings[scraper.schedule_time]" value="{{ $scraperSettings['schedule_time'] ?? '06:00' }}"
                                   class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm text-gray-900 dark:text-white transition-colors duration-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Timeout (giây)</label>
                            <input type="number" name="settings[scraper.timeout]" value="{{ $scraperSettings['timeout'] ?? 300 }}"
                                   class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm text-gray-900 dark:text-white transition-colors duration-300">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Số job đồng thời</label>
                            <input type="number" name="settings[scraper.concurrent_jobs]" value="{{ $scraperSettings['concurrent_jobs'] ?? 3 }}"
                                   class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm text-gray-900 dark:text-white transition-colors duration-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Giới hạn/ngày</label>
                            <input type="number" name="settings[scraper.daily_limit]" value="{{ $scraperSettings['daily_limit'] ?? 100 }}"
                                   class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm text-gray-900 dark:text-white transition-colors duration-300">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Apify API Token</label>
                        <input type="password" name="settings[scraper.apify_token]" value="{{ $scraperSettings['apify_token'] ?? '' }}"
                               class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-colors duration-300"
                               placeholder="Apify API token">
                    </div>
                </div>
            </div>
            
            <button type="submit" class="px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                </svg>
                Lưu cài đặt
            </button>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- General Settings -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-colors duration-300">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Chung</h3>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tên ứng dụng</label>
                        <input type="text" name="settings[general.app_name]" value="{{ $generalSettings['app_name'] ?? 'Land Search' }}"
                               class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm text-gray-900 dark:text-white transition-colors duration-300">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Múi giờ</label>
                        <select name="settings[general.timezone]" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm text-gray-900 dark:text-white transition-colors duration-300">
                            <option value="Asia/Ho_Chi_Minh" {{ ($generalSettings['timezone'] ?? '') === 'Asia/Ho_Chi_Minh' ? 'selected' : '' }}>Asia/Ho_Chi_Minh (UTC+7)</option>
                            <option value="Asia/Bangkok" {{ ($generalSettings['timezone'] ?? '') === 'Asia/Bangkok' ? 'selected' : '' }}>Asia/Bangkok (UTC+7)</option>
                            <option value="Asia/Hong_Kong" {{ ($generalSettings['timezone'] ?? '') === 'Asia/Hong_Kong' ? 'selected' : '' }}>Asia/Hong_Kong (UTC+8)</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Health Check -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-colors duration-300">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Kiểm tra kết nối</h3>
                <button type="button" id="testAiBtn" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                    </svg>
                    Test AI Provider
                </button>
                <div id="testResult" class="mt-4 hidden">
                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 transition-colors duration-300">
                        <p class="text-sm text-gray-700 dark:text-gray-300" id="testResultText"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
document.getElementById('testAiBtn')?.addEventListener('click', async () => {
    const btn = document.getElementById('testAiBtn');
    const result = document.getElementById('testResult');
    const resultText = document.getElementById('testResultText');
    
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Đang kiểm tra...';
    result.classList.add('hidden');
    
    try {
        const provider = document.querySelector('select[name="settings[ai.provider]"]')?.value || 'ollama';
        const payload = { provider };

        if (provider === 'ollama') {
            payload.ollama_model = document.querySelector('select[name="settings[ai.ollama_model]"]')?.value || '';
            payload.ollama_url = document.querySelector('input[name="settings[ai.ollama_url]"]')?.value || '';
            payload.ollama_api_key = document.querySelector('input[name="settings[ai.ollama_api_key]"]')?.value || '';
        }
        
        const response = await fetch('{{ route('dashboard.settings.testAiProvider') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        
        result.classList.remove('hidden');
        
        if (data.success) {
            resultText.innerHTML = '<span class="text-green-600 dark:text-green-400 font-medium">✓ Kết nối thành công</span><br><span class="text-xs text-gray-500 dark:text-gray-400">' + data.message + '</span>';
        } else {
            resultText.innerHTML = '<span class="text-red-600 dark:text-red-400 font-medium">✗ Kết nối thất bại</span><br><span class="text-xs text-gray-500 dark:text-gray-400">' + (data.error || data.message) + '</span>';
        }
    } catch (error) {
        result.classList.remove('hidden');
        resultText.innerHTML = '<span class="text-red-600 dark:text-red-400 font-medium">✗ Lỗi: ' + error.message + '</span>';
    }
    
    btn.disabled = false;
    btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg> Test AI Provider';
});
</script>
@endpush
