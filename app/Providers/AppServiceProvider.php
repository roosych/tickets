<?php

namespace App\Providers;

use App\Models\Ticket;
use App\Observers\TicketObserver;
use App\Services\TelegramNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TelegramNotificationService::class, function ($app) {
            return new TelegramNotificationService(config('services.telegram.chat_id'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Ticket::observe(TicketObserver::class);
    }
}
