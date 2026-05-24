@extends('layouts.dashboard')

@section('title', 'AI Pipeline - Processing Status')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">AI Pipeline Monitor</h1>
            <p class="text-gray-600 mt-1">Real-time processing status and statistics</p>
        </div>
        <div class="flex gap-3">
            <button onclick="runTest()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                Run Test Extraction
            </button>
            <button onclick="refreshStats()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                Refresh
            </button>
        </div>
    </div>

    <!-- Pipeline Status -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Pipeline Status</h2>
        </div>
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div id="pipeline-status" class="w-4 h-4 rounded-full bg-green-500"></div>
                    <span class="font-medium" id="pipeline-status-text">Operational</span>
                </div>
                <div class="text-sm text-gray-500">
                    Circuit Breaker: <span id="circuit-breaker-status">Closed</span>
                </div>
            </div>

            <!-- Pipeline Flow -->
            <div class="flex items-center justify-between">
                @foreach(['Preprocess', 'Prompt', 'Extract', 'Validate', 'Normalize', 'Store'] as $stage)
                <div class="flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mb-2">
                        <span class="text-blue-600 font-bold">{{ substr($stage, 0, 1) }}</span>
                    </div>
                    <span class="text-xs text-gray-600">{{ $stage }}</span>
                    <div class="w-full h-1 bg-gray-200 mt-2">
                        <div id="stage-{{ strtolower($stage) }}" class="h-full bg-green-500 transition-all" style="width: 100%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-500">Total Extractions</div>
            <div class="text-2xl font-bold text-blue-600" id="stat-total">0</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-500">Success Rate</div>
            <div class="text-2xl font-bold text-green-600" id="stat-success-rate">-</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-500">Avg Confidence</div>
            <div class="text-2xl font-bold text-purple-600" id="stat-avg-confidence">-</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-500">Avg Processing Time</div>
            <div class="text-2xl font-bold text-orange-600" id="stat-avg-time">-</div>
        </div>
    </div>

    <!-- Live Processing Log -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h2 class="text-lg font-semibold">Processing Log</h2>
            <button onclick="clearLog()" class="text-sm text-gray-500 hover:text-gray-700">Clear</button>
        </div>
        <div class="p-6">
            <div id="processing-log" class="h-64 overflow-y-auto font-mono text-sm space-y-1">
                <div class="text-gray-500">Waiting for processing...</div>
            </div>
        </div>
    </div>

    <!-- Test Form -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Test Extraction</h2>
        </div>
        <div class="p-6">
            <form onsubmit="testExtraction(event)">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Raw Content</label>
                    <textarea 
                        id="test-content"
                        class="w-full p-3 border rounded-lg h-32 font-mono text-sm"
                        placeholder="Paste real estate post content here..."
                    ></textarea>
                </div>
                <div class="flex gap-4">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        Extract
                    </button>
                    <select id="test-provider" class="border rounded-lg px-4 py-2">
                        <option value="">Auto (Best)</option>
                        <option value="gemini">Gemini</option>
                        <option value="ollama">Ollama</option>
                        <option value="openai">OpenAI</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Extraction Result -->
    <div id="extraction-result" class="bg-white rounded-lg shadow hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h2 class="text-lg font-semibold">Extraction Result</h2>
            <span id="result-confidence" class="px-3 py-1 rounded-full text-sm bg-green-100 text-green-800"></span>
        </div>
        <div class="p-6">
            <div id="result-content" class="font-mono text-sm whitespace-pre-wrap"></div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function addLog(message, type = 'info') {
    const log = document.getElementById('processing-log');
    const time = new Date().toLocaleTimeString();
    const color = {
        info: 'text-blue-600',
        success: 'text-green-600',
        error: 'text-red-600',
        warning: 'text-yellow-600'
    }[type] || 'text-gray-600';
    
    log.innerHTML = `<div class="${color}">[${time}] ${message}</div>` + log.innerHTML;
}

async function refreshStats() {
    try {
        const response = await fetch('/api/pipeline/stats');
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('stat-total').textContent = result.data.total || 0;
            document.getElementById('stat-success-rate').textContent = result.data.success_rate || '-';
            document.getElementById('stat-avg-confidence').textContent = result.data.avg_confidence || '-';
            document.getElementById('stat-avg-time').textContent = result.data.avg_time_ms || '-';
        }
    } catch (error) {
        console.error('Stats refresh error:', error);
    }
}

async function testExtraction(event) {
    event.preventDefault();
    
    const content = document.getElementById('test-content').value.trim();
    const provider = document.getElementById('test-provider').value;
    
    if (!content) {
        alert('Please enter content to extract');
        return;
    }
    
    addLog('Starting extraction...', 'info');
    
    try {
        const response = await fetch('/api/pipeline/extract', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ content, provider })
        });
        
        const result = await response.json();
        
        if (result.success) {
            addLog('Extraction completed', 'success');
            showResult(result.data);
        } else {
            addLog('Extraction failed: ' + result.error, 'error');
        }
    } catch (error) {
        addLog('Error: ' + error.message, 'error');
    }
}

function showResult(data) {
    const resultDiv = document.getElementById('extraction-result');
    const contentDiv = document.getElementById('result-content');
    const confidenceSpan = document.getElementById('result-confidence');
    
    resultDiv.classList.remove('hidden');
    contentDiv.textContent = JSON.stringify(data.normalized || data, null, 2);
    confidenceSpan.textContent = `Confidence: ${((data.confidence || 0) * 100).toFixed(1)}%`;
    confidenceSpan.className = `px-3 py-1 rounded-full text-sm ${
        (data.confidence || 0) >= 0.8 ? 'bg-green-100 text-green-800' :
        (data.confidence || 0) >= 0.5 ? 'bg-yellow-100 text-yellow-800' :
        'bg-red-100 text-red-800'
    }`;
}

function clearLog() {
    document.getElementById('processing-log').innerHTML = '<div class="text-gray-500">Log cleared</div>';
}

// Refresh stats on load
document.addEventListener('DOMContentLoaded', refreshStats);
</script>
@endpush
