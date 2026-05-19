<?php

namespace Modules\Advertising\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected $moduleNamespace = 'Modules\Advertising\Http\Controllers';

    /**
     * Called before routes are registered.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map(): void
    {
        $this->mapWebRoutes();
        $this->mapAdminRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * @return void
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(__DIR__ . '/../Routes/web.php');
    }

    /**
     * Define the admin routes for the application.
     *
     * @return void
     */
    protected function mapAdminRoutes(): void
    {
        Route::middleware(['web', 'auth', 'is_admin'])
            ->namespace($this->moduleNamespace . '\Admin')
            ->group(__DIR__ . '/../Routes/admin.php');
    }
}
