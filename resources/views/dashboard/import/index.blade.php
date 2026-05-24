@extends('layouts.dashboard')

@section('title', 'Import')

@section('breadcrumb')
    <span class="text-gray-700 dark:text-gray-300">Import</span>
@endsection

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Import dữ liệu</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Upload file JSON để xử lý</p>
    </div>
    <a href="{{ route('dashboard.import.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition-colors">
        <span class="material-symbols-outlined text-xl">add</span>
        Import mới
    </a>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Tổng lần import</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] ?? 0 }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Hoàn thành</p>
        <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] ?? 0 }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Đang xử lý</p>
        <p class="text-2xl font-bold text-yellow-600">{{ $stats['processing'] ?? 0 }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Thất bại</p>
        <p class="text-2xl font-bold text-red-600">{{ $stats['failed'] ?? 0 }}</p>
    </div>
</div>

<!-- Batches Table -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tên</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Nguồn</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">AI Provider</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tiến độ</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Trạng thái</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Ngày tạo</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($batches ?? [] as $batch)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $batch->name }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            {{ $batch->source }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $batch->ai_provider }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="w-32">
                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                <span>{{ $batch->processed_records ?? 0 }}/{{ $batch->total_records ?? 0 }}</span>
                                <span>{{ round($batch->progressPercent ?? 0) }}%</span>
                            </div>
                            <div class="h-2 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                                <div class="h-full bg-teal-500 rounded-full transition-all" style="width: {{ $batch->progressPercent ?? 0 }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($batch->status === 'completed')
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Hoàn thành</span>
                        @elseif($batch->status === 'processing')
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">Đang xử lý</span>
                        @elseif($batch->status === 'failed')
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Thất bại</span>
                        @else
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Chờ</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $batch->created_at->format('d/m/Y H:i') }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('dashboard.import.show', $batch->id) }}" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400" title="Chi tiết">
                                <span class="material-symbols-outlined">visibility</span>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <span class="material-symbols-outlined text-5xl text-gray-300 dark:text-gray-600">upload_file</span>
                        <p class="text-gray-500 dark:text-gray-400 mt-3">Chưa có lần import nào</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if(isset($batches) && $batches->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        {{ $batches->links() }}
    </div>
    @endif
</div>
@endsection
