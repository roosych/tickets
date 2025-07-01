<?php

namespace App\Console\Commands;

use App\Enums\TicketStatusEnum;
use App\Models\Ticket;
use App\Notifications\TicketReminderNotification;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class SendTicketReminders extends Command
{
    protected $signature = 'ticket:send-reminder';
    protected $description = 'Отправка напоминаний авторам открытых тикетов';

    public function handle()
    {
        $tickets = Ticket::where('status', TicketStatusEnum::DONE)->get();

        foreach ($tickets as $ticket) {
            if ($ticket->creator) {
                $ticket->creator->notify(new TicketReminderNotification($ticket));
            }
        }

        $this->info('Напоминания успешно отправлены.');
        return CommandAlias::SUCCESS;
    }
}
