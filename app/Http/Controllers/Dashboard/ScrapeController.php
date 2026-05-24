<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FacebookGroup;
use App\Models\ScrapeLog;
use App\Repositories\FacebookGroupRepository;
use App\Services\ScrapeService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class ScrapeController extends Controller
{
    public function __construct(
        protected FacebookGroupRepository $groupRepository,
        protected ScrapeService $scrapeService
    ) {}

    public function index(): View
    {
        $groups = $this->groupRepository->getAllCollection();
        $logs = ScrapeLog::with('facebookGroup')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $runningGroups = $groups->where('status', FacebookGroup::STATUS_RUNNING);
        $idleGroups = $groups->where('status', FacebookGroup::STATUS_IDLE)->where('enabled', true);

        return view('dashboard.scrape.index', [
            'groups' => $groups,
            'logs' => $logs,
            'runningGroups' => $runningGroups,
            'idleGroups' => $idleGroups,
        ]);
    }

    public function startAll(): \Illuminate\Http\RedirectResponse
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $groups = $this->groupRepository->getIdleEnabledGroups();

        foreach ($groups as $group) {
            $this->scrapeService->dispatchScrapeJob($group);
        }

        return redirect()
            ->route('dashboard.scrape.index')
            ->with('success', 'Đã khởi động scrape cho ' . $groups->count() . ' groups.');
    }

    public function startGroup(int $id): \Illuminate\Http\RedirectResponse
    {
        $group = $this->groupRepository->findById($id);
        
        if (!$group) {
            abort(404);
        }

        Gate::authorize('scrape', $group);

        $this->scrapeService->dispatchScrapeJob($group);

        return redirect()
            ->route('dashboard.scrape.index')
            ->with('success', 'Đã khởi động scrape cho group: ' . $group->name);
    }

    public function stopGroup(int $id): \Illuminate\Http\RedirectResponse
    {
        $group = $this->groupRepository->findById($id);
        
        if (!$group) {
            abort(404);
        }

        Gate::authorize('scrape', $group);

        $group->reset();

        return redirect()
            ->route('dashboard.scrape.index')
            ->with('success', 'Đã dừng scrape cho group: ' . $group->name);
    }
}
