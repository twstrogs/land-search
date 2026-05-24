@extends('layouts.dashboard')

@section('title', 'Scraped JSON')

@section('breadcrumb')
    <span class="text-gray-700 dark:text-gray-300">Scraped JSON</span>
@endsection

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Scraped JSON</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Xem dữ liệu JSON đã scrape từ Facebook groups</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Tổng record</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Hoàn thành</p>
        <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Bỏ qua</p>
        <p class="text-2xl font-bold text-yellow-600">{{ $stats['skipped'] }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Lỗi</p>
        <p class="text-2xl font-bold text-red-600">{{ $stats['failed'] }}</p>
    </div>
</div>

<form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-3">
    <input
        type="text"
        name="q"
        value="{{ request('q') }}"
        placeholder="Tìm theo nội dung/lỗi"
        class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm"
    >
    <select name="status" class="w-full px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm">
        <option value="">Tất cả trạng thái</option>
        @foreach(['pending' => 'Chờ', 'completed' => 'Hoàn thành', 'failed' => 'Lỗi', 'skipped' => 'Bỏ qua'] as $value => $label)
            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <button class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-sm font-medium">Lọc</button>
</form>

<div class="space-y-4">
    @forelse($records as $record)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between mb-2">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Batch:
                    <a class="text-teal-600 hover:underline" href="{{ route('dashboard.import.show', $record->import_batch_id) }}">
                        #{{ $record->import_batch_id }}
                    </a>
                </div>
                <span class="text-xs px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700">{{ $record->status }}</span>
            </div>

            <p class="text-sm text-gray-900 dark:text-gray-100 mb-3">{{ \Illuminate\Support\Str::limit($record->raw_content, 180) }}</p>

            @if($record->error_message)
                <p class="text-xs text-red-600 mb-3">{{ $record->error_message }}</p>
            @endif

            <details class="text-xs">
                <summary class="cursor-pointer text-gray-600 dark:text-gray-300">Xem JSON</summary>
                <pre class="mt-2 p-3 bg-gray-50 dark:bg-gray-900 rounded-lg overflow-auto">{{ json_encode($record->raw_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </details>
        </div>
    @empty
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-8 text-center text-gray-500">
            Chưa có JSON scrape nào.
        </div>
    @endforelse
</div>

@if($records->hasPages())
    <div class="mt-4">{{ $records->links() }}</div>
@endif
@endsection
