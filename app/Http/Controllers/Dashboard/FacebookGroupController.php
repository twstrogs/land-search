<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Repositories\FacebookGroupRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class FacebookGroupController extends Controller
{
    public function __construct(
        protected FacebookGroupRepository $groupRepository
    ) {}

    public function index(): View
    {
        $groups = $this->groupRepository->getAll();

        return view('dashboard.facebook-groups.index', [
            'groups' => $groups,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', \App\Models\FacebookGroup::class);

        return view('dashboard.facebook-groups.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('create', \App\Models\FacebookGroup::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'enabled' => 'boolean',
            'scrape_limit' => 'integer|min:1|max:100',
            'priority' => 'integer|min:0|max:100',
        ]);

        $this->groupRepository->create($validated);

        return redirect()
            ->route('dashboard.facebook-groups.index')
            ->with('success', 'Facebook Group đã được thêm thành công.');
    }

    public function edit(int $id): View
    {
        $group = $this->groupRepository->findById($id);

        if (!$group) {
            abort(404);
        }

        Gate::authorize('update', $group);

        return view('dashboard.facebook-groups.edit', [
            'group' => $group,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $group = $this->groupRepository->findById($id);

        if (!$group) {
            abort(404);
        }

        Gate::authorize('update', $group);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'enabled' => 'boolean',
            'scrape_limit' => 'integer|min:1|max:100',
            'priority' => 'integer|min:0|max:100',
        ]);

        $this->groupRepository->update($group, $validated);

        return redirect()
            ->route('dashboard.facebook-groups.index')
            ->with('success', 'Facebook Group đã được cập nhật.');
    }

    public function destroy(int $id)
    {
        $group = $this->groupRepository->findById($id);

        if (!$group) {
            abort(404);
        }

        Gate::authorize('delete', $group);

        $this->groupRepository->delete($group);

        return redirect()
            ->route('dashboard.facebook-groups.index')
            ->with('success', 'Facebook Group đã được xóa.');
    }
}
