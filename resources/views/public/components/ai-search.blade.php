{{-- AI Search Component - Modern Real Estate Style --}}
{{-- Usage: @include('public.components.ai-search') --}}

@php
    $suggestions = [
        'đất đầu tư gần Samsung',
        'nhà dưới 2 tỷ gần trung tâm',
        'đất ngõ ô tô phường Túc Duyên',
        'nhà đẹp để ở',
        'đất tăng giá tốt',
        'chung cư dưới 1.5 tỷ',
    ];
    
    $exampleQueries = [
        ['icon' => 'factory', 'text' => 'Gần KCN Samsung'],
        ['icon' => 'school', 'text' => 'Gần trường học'],
        ['icon' => 'directions_car', 'text' => 'Ngõ ô tô'],
        ['icon' => 'home', 'text' => 'Mặt tiền đường lớn'],
    ];
@endphp

<div class="bg-gradient-to-r from-teal-500 to-teal-600 rounded-2xl p-6 lg:p-8 text-white">
    <div class="flex items-start gap-4 mb-6">
        <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-2xl">psychology</span>
        </div>
        <div>
            <h3 class="text-xl font-bold mb-1">Tìm kiếm thông minh với AI</h3>
            <p class="text-white/80 text-sm">Nhập yêu cầu bằng ngôn ngữ tự nhiên, AI sẽ hiểu và tìm BĐS phù hợp</p>
        </div>
    </div>
    
    <!-- AI Search Input -->
    <form action="{{ route('search') }}" method="GET" class="relative" id="aiSearchForm">
        <div class="relative">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-teal-300">auto_awesome</span>
            <input type="text" 
                   id="aiSearchInput"
                   name="keyword"
                   class="w-full pl-12 pr-32 py-4 bg-white rounded-xl text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-white/30 transition-all"
                   placeholder="VD: đất đầu tư gần Samsung dưới 2 tỷ..."
                   autocomplete="off">
            <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg transition-colors flex items-center gap-2">
                <span>Tìm</span>
                <span class="material-symbols-outlined">arrow_forward</span>
            </button>
        </div>
        
        <!-- AI Suggestions Dropdown -->
        <div id="aiSuggestions" class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden z-20 hidden">
            <div class="p-3 border-b border-slate-100 dark:border-slate-700">
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">AI gợi ý:</p>
            </div>
            <div class="max-h-64 overflow-y-auto" id="suggestionList">
                @foreach($suggestions as $suggestion)
                <button type="button" onclick="selectSuggestion('{{ $suggestion }}')" 
                        class="w-full px-4 py-3 text-left text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors flex items-center gap-3">
                    <span class="material-symbols-outlined text-teal-500">trending_up</span>
                    <span>{{ $suggestion }}</span>
                </button>
                @endforeach
            </div>
        </div>
    </form>
    
    <!-- Example Queries -->
    <div class="mt-4">
        <p class="text-xs text-white/60 mb-2">Thử hỏi AI:</p>
        <div class="flex flex-wrap gap-2">
            @foreach($exampleQueries as $example)
            <button type="button" onclick="selectSuggestion('{{ $example['text'] }}')" 
                    class="px-3 py-1.5 bg-white/10 hover:bg-white/20 rounded-lg text-sm transition-colors flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm"> {{ $example['icon'] }}</span>
                <span>{{ $example['text'] }}</span>
            </button>
            @endforeach
        </div>
    </div>
</div>

<style>
    #aiSearchInput:focus {
        box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.3);
    }
</style>

<script>
    const aiInput = document.getElementById('aiSearchInput');
    const aiSuggestions = document.getElementById('aiSuggestions');
    const suggestionList = document.getElementById('suggestionList');
    
    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // AI intent analysis
    function analyzeIntent(query) {
        query = query.toLowerCase();
        const results = [];
        
        const priceMatch = query.match(/dưới\s*(\d+)\s*(tỷ|triệu|tr)/);
        if (priceMatch) {
            const value = parseInt(priceMatch[1]);
            const unit = priceMatch[2];
            if (unit === 'tỷ' || unit === 'ty') {
                results.push({ type: 'price_max', value: value * 1000 });
            } else {
                results.push({ type: 'price_max', value: value });
            }
        }
        
        if (query.includes('lớn') || query.includes('rộng')) {
            results.push({ type: 'sort', value: 'area_desc' });
        }
        
        if (query.includes('samsung') || query.includes('kcn')) {
            results.push({ type: 'location', value: 'Samsung' });
        }
        if (query.includes('trung tâm')) {
            results.push({ type: 'location', value: 'TP Thái Nguyên' });
        }
        
        if (query.includes('đất')) {
            results.push({ type: 'property_type', value: 'Đất nền/Đất thổ cư' });
        }
        if (query.includes('nhà')) {
            results.push({ type: 'property_type', value: 'Nhà phố/Nhà riêng' });
        }
        if (query.includes('chung cư')) {
            results.push({ type: 'property_type', value: 'Căn hộ chung cư' });
        }
        
        if (query.includes('ô tô') || query.includes('oto')) {
            results.push({ type: 'amenity', value: 'ngo_o_to' });
        }
        if (query.includes('mặt tiền')) {
            results.push({ type: 'amenity', value: 'mat_tien' });
        }
        
        return results;
    }
    
    // Show suggestions
    const showSuggestions = debounce((query) => {
        if (query.length < 3) {
            aiSuggestions.classList.add('hidden');
            return;
        }
        
        const suggestions = generateDynamicSuggestions(query);
        
        if (suggestions.length > 0) {
            suggestionList.innerHTML = suggestions.map(s => `
                <button type="button" onclick="selectSuggestion('${s.replace(/'/g, "\\'")}')" 
                        class="w-full px-4 py-3 text-left text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors flex items-center gap-3">
                    <span class="material-symbols-outlined text-teal-500">auto_awesome</span>
                    <span>${s}</span>
                </button>
            `).join('');
            aiSuggestions.classList.remove('hidden');
        } else {
            aiSuggestions.classList.add('hidden');
        }
    }, 300);
    
    function generateDynamicSuggestions(query) {
        const suggestions = [];
        query = query.toLowerCase();
        
        if (!query.includes('samsung') && (query.includes('đất') || query.includes('nhà'))) {
            suggestions.push('Đất đầu tư gần Samsung ' + extractPrice(query));
        }
        
        if (!query.includes('trung tâm') && (query.includes('mua') || query.includes('ở'))) {
            suggestions.push('Nhà đẹp gần trung tâm ' + extractPrice(query));
        }
        
        if (!query.includes('ô tô') && query.includes('đất')) {
            suggestions.push('Đất ngõ ô tô ' + extractPrice(query));
        }
        
        if (!query.includes('1 tỷ') && (query.includes('dưới') || query.includes('ít tiền'))) {
            suggestions.push('BĐS dưới 1 tỷ tại Thái Nguyên');
        }
        
        if (query.includes('đầu tư')) {
            suggestions.push('Đất đầu tư tăng giá tốt');
        }
        
        return suggestions;
    }
    
    function extractPrice(query) {
        const match = query.match(/dưới\s*(\d+)\s*(tỷ|triệu|tr)?/);
        if (match) {
            return 'dưới ' + match[1] + (match[2] || 'tỷ');
        }
        return '';
    }
    
    function selectSuggestion(text) {
        aiInput.value = text;
        aiSuggestions.classList.add('hidden');
        analyzeIntent(text);
        document.getElementById('aiSearchForm').submit();
    }
    
    aiInput?.addEventListener('input', (e) => {
        showSuggestions(e.target.value);
    });
    
    aiInput?.addEventListener('focus', () => {
        if (aiInput.value.length >= 3) {
            showSuggestions(aiInput.value);
        }
    });
    
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#aiSearchForm')) {
            aiSuggestions?.classList.add('hidden');
        }
    });
    
    aiInput?.addEventListener('keydown', (e) => {
        if (!aiSuggestions.classList.contains('hidden')) {
            const buttons = suggestionList.querySelectorAll('button');
            const currentIndex = Array.from(buttons).findIndex(b => b === document.activeElement);
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                const next = buttons[currentIndex + 1] || buttons[0];
                next?.focus();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                const prev = buttons[currentIndex - 1] || buttons[buttons.length - 1];
                prev?.focus();
            } else if (e.key === 'Enter' && document.activeElement.tagName === 'BUTTON') {
                e.preventDefault();
                document.activeElement.click();
            } else if (e.key === 'Escape') {
                aiSuggestions.classList.add('hidden');
            }
        }
    });
</script>
