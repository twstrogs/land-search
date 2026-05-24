<?php

namespace App\Policies;

use App\Models\FacebookGroup;
use App\Models\User;

class FacebookGroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, FacebookGroup $group): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, FacebookGroup $group): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, FacebookGroup $group): bool
    {
        return $user->isAdmin();
    }

    public function scrapeAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function scrape(User $user, FacebookGroup $group): bool
    {
        return $user->isAdmin() && $group->enabled;
    }
}
