<?php

namespace App\Repositories;

use App\Models\FacebookGroup;
use Illuminate\Database\Eloquent\Collection;

class FacebookGroupRepository
{
    public function getAll(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return FacebookGroup::orderBy('priority', 'desc')->paginate(20);
    }

    public function getAllCollection(): Collection
    {
        return FacebookGroup::orderBy('priority', 'desc')->get();
    }

    public function getEnabled(): Collection
    {
        return FacebookGroup::where('enabled', true)
            ->orderBy('priority', 'desc')
            ->get();
    }

    public function findById(int $id): ?FacebookGroup
    {
        return FacebookGroup::find($id);
    }

    public function create(array $data): FacebookGroup
    {
        return FacebookGroup::create($data);
    }

    public function update(FacebookGroup $group, array $data): FacebookGroup
    {
        $group->update($data);
        return $group;
    }

    public function delete(FacebookGroup $group): void
    {
        $group->delete();
    }

    public function getRunningGroups(): Collection
    {
        return FacebookGroup::where('status', FacebookGroup::STATUS_RUNNING)->get();
    }

    public function getIdleEnabledGroups(): Collection
    {
        return FacebookGroup::where('enabled', true)
            ->where('status', FacebookGroup::STATUS_IDLE)
            ->orderBy('priority', 'desc')
            ->get();
    }
}
