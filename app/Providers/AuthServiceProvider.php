<?php

namespace App\Providers;

use App\Models\FacebookGroup;
use App\Policies\FacebookGroupPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        FacebookGroup::class => FacebookGroupPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
