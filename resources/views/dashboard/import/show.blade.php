@extends('layouts.dashboard')

@section('title', 'Chi tiết Import')

@section('breadcrumb')
    <a href="{{ route('dashboard.import.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">Import</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-700 dark:text-gray-300">{{ $batch->name }}</span>
@endsection

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $batch->name }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            @if($batch->status === 'completed')
                Hoàn thành
            @elseif($batch->status === 'processing')
                Đang xử lý
            @elseif($batch->status === 'failed')
                Thất bại
            @else
                Đang chờ
            @endif
        </p>
    </div>
    <a href="{{ route('dashboard.import.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
        <span class="material-symbols-outlined">arrow_back</span>
        Quay lại
    </a>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Tổng số</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $batch->total_records ?? 0 }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Đã xử lý</p>
        <p class="text-2xl font-bold text-green-600">{{ $batch->processed_records ?? 0 }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Thất bại</p>
        <p class="text-2xl font-bold text-red-600">{{ $batch->failed_records ?? 0 }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Trùng lặp</p>
        <p class="text-2xl font-bold text-yellow-600">{{ $batch->duplicate_records ?? 0 }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Bỏ qua</p>
        <p class="text-2xl font-bold text-gray-600">{{ $batch->skipped_records ?? 0 }}</p>
    </div>
</div>

<!-- Progress Bar -->
<div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
    <div class="flex justify-between text-sm mb-2">
        <span class="text-gray-600 dark:text-gray-400">Tiến độ</span>
        <span class="font-medium text-gray-900 dark:text-white">{{ round($batch->progressPercent ?? 0) }}%</span>
    </div>
    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
        <div class="h-full hero-gradient rounded-full transition-all duration-500" style="width: {{ $batch->progressPercent ?? 0 }}%"></div>
    </div>
</div>

<!-- Records Table -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Nội dung</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Trạng thái</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Post</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Lỗi</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Thời gian</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($records ?? [] as $record)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-900 dark:text-white max-w-[300px] truncate">
                            {{ Str::limit($record->raw_content, 80) }}
                        </p>
                    </td>
                    <td class="px-6 py-4">
                        @if($record->status === 'completed')
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Hoàn thành</span>
                        @elseif($record->status === 'processing')
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">Đang xử lý</span>
                        @elseif($record->status === 'failed')
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Thất bại</span>
                        @else
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">Chờ</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($record->post)
                            <a href="{{ route('dashboard.posts.edit', $record->post_id) }}" class="text-sm text-teal-600 dark:text-teal-400 hover:underline">
                                #{{ $record->post_id }}
                            </a>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($record->error_message)
                            <span class="text-sm text-red-500 truncate max-w-[200px] block" title="{{ $record->error_message }}">
                                {{ Str::limit($record->error_message, 30) }}
                            </span>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $record->created_at->format('d/m/Y H:i:s') }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <span class="material-symbols-outlined text-5xl text-gray-300 dark:text-gray-600">inbox</span>
                        <p class="text-gray-500 dark:text-gray-400 mt-3">Chưa có record nào</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if(isset($records) && $records->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        {{ $records->links() }}
    </div>
    @endif
</div>

<style>
.hero-gradient {
    background: linear-gradient(135deg, #0d9488 0%, #059669 50%, #10b981 100%);
}
</style>
@endsection
