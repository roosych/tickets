<?php

namespace App\Providers;

use App\Models\Language;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/cabinet';


    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix(Language::routePrefix())
                ->group(function () {
                    Route::middleware('web')
                        ->group(base_path('routes/web.php'));

//                    Route::middleware('web')
//                        ->group(base_path('routes/auth.php'));
                });
//            Route::middleware('api')
//                ->prefix('api')
//                ->group(base_path('routes/api.php'));
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
