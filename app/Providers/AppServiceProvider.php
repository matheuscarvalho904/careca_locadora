<?php

namespace App\Providers;

use App\Models\Branch;
use App\Models\Company;
use App\Observers\BranchObserver;
use App\Observers\CompanyObserver;

use App\Http\Middleware\ResolveTenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Company::observe(CompanyObserver::class);
        Branch::observe(BranchObserver::class);
        $this->configureDefaults();
        $this->configureLivewireTenancy();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Apply the tenant resolver before every Livewire update request.
     */
    protected function configureLivewireTenancy(): void
    {
        Livewire::addPersistentMiddleware([
            ResolveTenantContext::class,
        ]);

        Livewire::setUpdateRoute(function ($handle, $path) {
            return Route::post($path, $handle)
                ->middleware([
                    'web',
                    ResolveTenantContext::class,
                ]);
        });
    }
}
