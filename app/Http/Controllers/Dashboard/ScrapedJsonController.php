<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\SourceRecord;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScrapedJsonController extends Controller
{
    public function index(Request $request): View
    {
        $query = SourceRecord::with(['importBatch', 'post'])
            ->whereHas('importBatch', function ($q) {
                $q->where('source', 'facebook_scrape');
            })
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('q')) {
            $keyword = $request->string('q')->toString();
            $query->where(function ($q) use ($keyword) {
                $q->where('raw_content', 'like', "%{$keyword}%")
                    ->orWhere('error_message', 'like', "%{$keyword}%");
            });
        }

        $records = $query->paginate(30)->withQueryString();

        return view('dashboard.scraped-json.index', [
            'records' => $records,
            'stats' => [
                'total' => SourceRecord::whereHas('importBatch', fn ($q) => $q->where('source', 'facebook_scrape'))->count(),
                'completed' => SourceRecord::where('status', 'completed')->whereHas('importBatch', fn ($q) => $q->where('source', 'facebook_scrape'))->count(),
                'skipped' => SourceRecord::where('status', 'skipped')->whereHas('importBatch', fn ($q) => $q->where('source', 'facebook_scrape'))->count(),
                'failed' => SourceRecord::where('status', 'failed')->whereHas('importBatch', fn ($q) => $q->where('source', 'facebook_scrape'))->count(),
            ],
        ]);
    }
}
