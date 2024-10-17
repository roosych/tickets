<?php

namespace App\Providers;

use App\Events\TicketEvent;
use App\Events\TicketUpdated;
use App\Listeners\SaveUserPhotoListener;
use App\Listeners\SendTicketNotificationListener;
use App\Listeners\TicketEventListener;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use LdapRecord\Laravel\Events\Import\Saved;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
//        Registered::class => [
//            SendEmailVerificationNotification::class,
//        ],

        // когда сохраняется юзер LdapRecord\Laravel\Events\Import\Saved
        // слушатель который сохраняет фото юзера в папку
        //todo сохранение юзера в таблицу users (данные с auth.php)
        Saved::class => [
            SaveUserPhotoListener::class,
        ],

        TicketEvent::class => [
            TicketEventListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {

        Event::listen(Authenticated::class, function ($event) {
            //dd($event->user);
            $event->user->update(['last_login' => now()]);
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
