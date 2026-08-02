<?php

namespace App\Providers;

use App\Contracts\TenantContext;
use App\Support\TenantContextManager;
use Illuminate\Support\ServiceProvider;

final class FoundationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(
            TenantContext::class,
            TenantContextManager::class
        );
    }

    public function boot(): void
    {
        //
    }
}
