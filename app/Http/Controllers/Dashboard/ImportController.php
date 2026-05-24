<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessImportJob;
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
        $batches->getCollection()->each->syncComputedStatus();

        return view('dashboard.import.index', [
            'batches' => $batches,
            'stats' => [
                'total' => ImportBatch::count(),
                'completed' => ImportBatch::where('status', 'completed')->count(),
                'processing' => ImportBatch::whereIn('status', ['processing', 'pending'])->count(),
                'failed' => ImportBatch::where('status', 'failed')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $providers = AiServiceFactory::getAvailableProviders();

        return view('dashboard.import.create', [
            'providers' => $providers,
        ]);
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

        ProcessImportJob::dispatch($batch, $data);

        return redirect()
            ->route('dashboard.import.index')
            ->with('success', 'Đang xử lý import. Batch ID: ' . $batch->id);
    }

    public function show(int $id): View
    {
        $batch = ImportBatch::with('user')->findOrFail($id);
        $batch->syncComputedStatus();
        $records = $batch->sourceRecords()
            ->latest()
            ->paginate(50);

        return view('dashboard.import.show', [
            'batch' => $batch,
            'records' => $records,
        ]);
    }
}
