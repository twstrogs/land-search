<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Setting $setting): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Setting $setting): bool
    {
        return $user->isAdmin();
    }

    public function updateGroup(User $user, string $group): bool
    {
        return $user->isAdmin();
    }
}
