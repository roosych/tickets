<?php

namespace App\Listeners;

use App\Events\TicketEvent;
use App\Jobs\SendTicketNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class TicketEventListener implements ShouldQueue
{
    public function handle(TicketEvent $event): void
    {
        foreach ($event->recipients as $user) {
            SendTicketNotification::dispatch($user, $event->ticket, $event->action, $event->additionalData);
        }
    }
}
