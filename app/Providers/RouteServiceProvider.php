<?php

namespace App\Providers;

use App\Http\Controllers\Api\IntegrationTicketController;
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
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            // Внешние системы создают и сверяют тикеты пачками (ПО на сотню принтеров), им нужен больший лимит.
            if ($request->user()?->tokenCan(IntegrationTicketController::ABILITY)) {
                return Limit::perMinute(600)->by('integration:'.$request->user()->id);
            }

            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
