@extends('layouts.dashboard')

@section('title', 'AI Evaluation Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">AI Evaluation Dashboard</h1>
            <p class="text-gray-600 mt-1">Benchmark and compare extraction methods</p>
        </div>
        <button onclick="runBenchmark()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            Run Full Benchmark
        </button>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-500">Best Method</div>
            <div class="text-2xl font-bold text-green-600" id="best-method">-</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-500">Best F1 Score</div>
            <div class="text-2xl font-bold text-blue-600" id="best-f1">-</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-500">Total Evaluations</div>
            <div class="text-2xl font-bold text-purple-600" id="total-evaluations">-</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-500">Avg Processing Time</div>
            <div class="text-2xl font-bold text-orange-600" id="avg-time">-</div>
        </div>
    </div>

    <!-- Benchmark Results Table -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Method Comparison</h2>
        </div>
        <div class="p-6">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-gray-500 text-sm">
                        <th class="pb-3">Method</th>
                        <th class="pb-3">Accuracy</th>
                        <th class="pb-3">Precision</th>
                        <th class="pb-3">Recall</th>
                        <th class="pb-3">F1-Score</th>
                        <th class="pb-3">Avg Time</th>
                        <th class="pb-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="results-table">
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500">
                            Run benchmark to see results
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Field Metrics Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Per-Field Performance -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Per-Field F1 Scores</h2>
            </div>
            <div class="p-6">
                <div id="field-metrics" class="space-y-4">
                    <div class="text-center text-gray-500 py-8">
                        Select a method to see field breakdown
                    </div>
                </div>
            </div>
        </div>

        <!-- Method Comparison Chart -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Performance Comparison</h2>
            </div>
            <div class="p-6">
                <canvas id="comparisonChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Historical Trend -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Evaluation History</h2>
        </div>
        <div class="p-6">
            <div id="history-chart" class="h-64">
                <canvas id="historyCanvas" width="800" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let comparisonChart = null;
let historyChart = null;

async function runBenchmark() {
    try {
        const response = await fetch('/api/evaluation/benchmark', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            updateResults(result.data);
            updateBestMethod(result.data);
        } else {
            alert('Benchmark failed: ' + result.error);
        }
    } catch (error) {
        console.error('Benchmark error:', error);
        alert('Benchmark failed: ' + error.message);
    }
}

function updateResults(data) {
    const tbody = document.getElementById('results-table');
    
    if (!data.results || Object.keys(data.results).length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-gray-500">No results</td></tr>';
        return;
    }
    
    let html = '';
    for (const [method, result] of Object.entries(data.results)) {
        html += `
            <tr class="border-t">
                <td class="py-3 font-medium">${method}</td>
                <td class="py-3">${(result.metrics.accuracy * 100).toFixed(1)}%</td>
                <td class="py-3">${(result.metrics.precision * 100).toFixed(1)}%</td>
                <td class="py-3">${(result.metrics.recall * 100).toFixed(1)}%</td>
                <td class="py-3 font-semibold text-blue-600">${(result.metrics.f1_score * 100).toFixed(1)}%</td>
                <td class="py-3">${result.processingTimeMs}ms</td>
                <td class="py-3">
                    <button onclick="viewFieldMetrics('${method}')" class="text-blue-600 hover:underline">Details</button>
                </td>
            </tr>
        `;
    }
    
    tbody.innerHTML = html;
    
    // Update chart
    updateComparisonChart(data);
}

function updateBestMethod(data) {
    if (!data.results) return;
    
    let bestMethod = null;
    let bestF1 = -1;
    
    for (const [method, result] of Object.entries(data.results)) {
        if (result.metrics.f1_score > bestF1) {
            bestF1 = result.metrics.f1_score;
            bestMethod = method;
        }
    }
    
    document.getElementById('best-method').textContent = bestMethod || '-';
    document.getElementById('best-f1').textContent = bestF1 > 0 ? (bestF1 * 100).toFixed(1) + '%' : '-';
}

function updateComparisonChart(data) {
    const ctx = document.getElementById('comparisonChart').getContext('2d');
    
    const methods = Object.keys(data.results || {});
    const metrics = ['accuracy', 'precision', 'recall', 'f1_score'];
    const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444'];
    
    const datasets = metrics.map((metric, i) => ({
        label: metric.charAt(0).toUpperCase() + metric.slice(1).replace('_', ' '),
        data: methods.map(m => (data.results[m].metrics[metric] * 100).toFixed(1)),
        backgroundColor: colors[i],
        borderRadius: 4,
    }));
    
    if (comparisonChart) {
        comparisonChart.destroy();
    }
    
    comparisonChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: methods.map(m => m.replace('llm_', '').toUpperCase()),
            datasets: datasets
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: { display: true, text: 'Score (%)' }
                }
            }
        }
    });
}

function viewFieldMetrics(method) {
    console.log('View field metrics for:', method);
    // Could open a modal with detailed field breakdown
}

async function loadHistory() {
    try {
        const response = await fetch('/api/evaluation/history?limit=20');
        const result = await response.json();
        
        if (result.success) {
            updateHistoryChart(result.data);
        }
    } catch (error) {
        console.error('History load error:', error);
    }
}

function updateHistoryChart(data) {
    const ctx = document.getElementById('historyCanvas').getContext('2d');
    
    const methods = [...new Set(data.map(d => d.method))];
    const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'];
    
    const datasets = methods.map((method, i) => {
        const methodData = data.filter(d => d.method === method);
        return {
            label: method,
            data: methodData.map(d => ({
                x: new Date(d.created_at),
                y: (d.f1_score * 100).toFixed(1)
            })),
            borderColor: colors[i % colors.length],
            fill: false,
            tension: 0.3
        };
    });
    
    if (historyChart) {
        historyChart.destroy();
    }
    
    historyChart = new Chart(ctx, {
        type: 'line',
        data: { datasets },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                x: { type: 'time' },
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: { display: true, text: 'F1 Score (%)' }
                }
            }
        }
    });
}

// Load data on page load
document.addEventListener('DOMContentLoaded', () => {
    loadHistory();
});
</script>
@endpush
