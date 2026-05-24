<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ImportBatch;
use App\Services\AI\AiServiceFactory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function index(): View
    {
        $batches = ImportBatch::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('imports.index', compact('batches'));
    }

    public function create(): View
    {
        $providers = AiServiceFactory::getAvailableProviders();

        return view('imports.create', compact('providers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:json,txt|max:10240',
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->with('error', 'File không hợp lệ. Vui lòng upload file JSON.');
        }

        $batch = ImportBatch::create([
            'user_id' => auth()->id(),
            'name' => $file->getClientOriginalName(),
            'source' => 'manual_upload',
            'ai_provider' => AiServiceFactory::getDefaultProvider(),
            'total_records' => is_array($data) ? count($data) : 0,
            'status' => 'pending',
        ]);

        \App\Jobs\ProcessImportJob::dispatch($batch, $data);

        return redirect()
            ->route('imports.show', $batch->id)
            ->with('success', 'Import đang được xử lý!');
    }

    public function show(int $id): View
    {
        $batch = ImportBatch::with(['user', 'sourceRecords'])->findOrFail($id);

        return view('imports.show', compact('batch'));
    }

    public function status(int $id): \Illuminate\Http\JsonResponse
    {
        $batch = ImportBatch::findOrFail($id);

        return response()->json([
            'status' => $batch->status,
            'processed_records' => $batch->processed_records,
            'failed_records' => $batch->failed_records,
            'skipped_records' => $batch->skipped_records,
            'duplicate_records' => $batch->duplicate_records,
            'total_records' => $batch->total_records,
        ]);
    }

    public function retryFailed(Request $request, int $id)
    {
        $batch = ImportBatch::findOrFail($id);

        $failedRecords = $batch->sourceRecords()
            ->where('status', 'failed')
            ->get();

        foreach ($failedRecords as $record) {
            \App\Jobs\ProcessSourceRecordJob::dispatch($batch, json_decode($record->raw_json, true));
            $record->update(['status' => 'pending']);
        }

        $batch->update([
            'status' => 'processing',
            'failed_records' => 0,
        ]);

        return back()->with('success', 'Đã thêm ' . $failedRecords->count() . ' records vào queue!');
    }

    public function testProvider(Request $request)
    {
        $health = AiServiceFactory::checkHealth($request->get('provider'));

        return response()->json($health);
    }

    public function checkHealth(Request $request)
    {
        $providers = ['ollama', 'gemini', 'openai'];
        $results = [];

        foreach ($providers as $provider) {
            $results[$provider] = AiServiceFactory::checkHealth($provider);
        }

        return response()->json($results);
    }
}
