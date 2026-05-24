<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ImportBatch;
use App\Models\Setting;
use App\Models\ScrapeLog;
use App\Services\ApifyService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScraperController extends Controller
{
    public function index(): View
    {
        $apifyToken = Setting::getValue('scraper.apify_token');
        
        return view('scraper.index', compact('apifyToken'));
    }

    public function scrape(Request $request, ApifyService $apifyService)
    {
        $validated = $request->validate([
            'group_url' => 'required|url',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $apifyToken = Setting::getValue('scraper.apify_token');
        
        if (!$apifyToken) {
            return back()->with('error', 'Apify token chưa được cấu hình!');
        }

        try {
            $actor = Setting::getValue('scraper.apify_actor', 'apify/facebook-groups-scraper');
            $actorPath = str_replace('/', '~', trim((string) $actor));

            $runResponse = \Illuminate\Support\Facades\Http::withToken($apifyToken)
                ->post("https://api.apify.com/v2/acts/{$actorPath}/run-sync", [
                    'groupUrls' => [$validated['group_url']],
                    'resultsLimit' => $validated['limit'] ?? 20,
                ]);

            if (!$runResponse->successful()) {
                return back()->with('error', 'Không thể khởi động scraper!');
            }

            $runResult = $runResponse->json();
            $runId = $runResult['data']['id'] ?? null;

            ScrapeLog::info('scrape_started', "Scraper started for: {$validated['group_url']}", null, null, [
                'run_id' => $runId,
            ]);

            return back()->with('success', 'Scraper đã được khởi động! Run ID: ' . $runId);

        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function checkRunStatus(Request $request)
    {
        $validated = $request->validate([
            'run_id' => 'required|string',
        ]);

        $apifyToken = Setting::getValue('scraper.apify_token');

        $response = \Illuminate\Support\Facades\Http::withToken($apifyToken)
            ->get("https://api.apify.com/v2/actor-runs/{$validated['run_id']}");

        if (!$response->successful()) {
            return response()->json(['error' => 'Không thể kiểm tra trạng thái']);
        }

        $data = $response->json();

        return response()->json([
            'status' => $data['data']['status'] ?? 'unknown',
            'started_at' => $data['data']['startedAt'] ?? null,
            'finished_at' => $data['data']['finishedAt'] ?? null,
        ]);
    }

    public function getDataset(Request $request)
    {
        $validated = $request->validate([
            'dataset_id' => 'required|string',
        ]);

        $apifyToken = Setting::getValue('scraper.apify_token');

        $response = \Illuminate\Support\Facades\Http::withToken($apifyToken)
            ->get("https://api.apify.com/v2/datasets/{$validated['dataset_id']}/items");

        if (!$response->successful()) {
            return response()->json(['error' => 'Không thể lấy dataset']);
        }

        return response()->json($response->json());
    }

    public function importScrapedData(Request $request)
    {
        $validated = $request->validate([
            'data' => 'required|array',
        ]);

        $data = $validated['data'];
        $batch = ImportBatch::create([
            'user_id' => auth()->id(),
            'name' => 'Apify Scrape - ' . now()->format('Y-m-d H:i:s'),
            'source' => 'apify_scrape',
            'ai_provider' => Setting::getValue('ai.provider'),
            'total_records' => count($data),
            'status' => 'pending',
        ]);

        \App\Jobs\ProcessImportJob::dispatch($batch, $data);

        return redirect()
            ->route('imports.show', $batch->id)
            ->with('success', 'Đang xử lý import!');
    }
}
