<?php

namespace App\Console\Commands;

use App\Enums\TicketStatusEnum;
use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class NotifyUrgentTickets extends Command
{
    protected $signature = 'tickets:notify-urgent';
    protected $description = 'Notify urgent tickets';
    protected int $maxMessageLength = 4096;

    public function handle()
    {
        $openUrgentTickets = Ticket::where('status', TicketStatusEnum::OPENED)
            ->where('priorities_id', 1)
            ->with('performer')
            ->get();

        if ($openUrgentTickets->isEmpty()) {
            $this->info('Нет открытых срочных тикетов.');
            return;
        }

        $ticketsWithoutPerformer = $openUrgentTickets->whereNull('performer');
        $ticketsWithPerformer = $openUrgentTickets->whereNotNull('performer');

        $this->sendNotificationForTicketsWithoutPerformer($ticketsWithoutPerformer);
        $this->sendNotificationForTicketsWithPerformer($ticketsWithPerformer);

        $this->info('Отчеты по открытым срочным тикетам отправлены в Telegram.');
    }

    private function sendNotificationForTicketsWithoutPerformer($tickets)
    {
        if ($tickets->isEmpty()) {
            return;
        }

        $message = "🔥 <b>Срочные тикеты:</b>\n\n";
        $ticketLinks = $tickets->map(function($ticket) {
            return $this->formatTicketLink($ticket->id);
        })->implode(', ');

        $message .= $ticketLinks;

        $this->sendTelegramNotification($message);
    }

    private function sendNotificationForTicketsWithPerformer($tickets)
    {
        if ($tickets->isEmpty()) {
            return;
        }

        $openingMessages = [
            "Если Чак Норрис видит срочный тикет, время замедляется, чтобы он успел его закрыть. Тебе тоже стоит поторопиться!",
            "Срочный тикет у Чака Норриса решается до того, как становится срочным. Тебе нужно действовать немедленно!",
            "Для Чака Норриса срочные задачи — это как утренняя зарядка. Этот тикет ждет тебя прямо сейчас!",
            "Срочные задачи не осмеливаются задерживаться у Чака Норриса. Этот тикет должен быть закрыт немедленно!",
            "Если ты видишь срочный тикет, знаешь, что Чак Норрис уже на пути его закрыть. Давай не будем отставать!",
            "Срочный тикет не ждет! Он словно тень Чака Норриса — всегда рядом. Успей закрыть его, пока он не исчез!",
            "Срочный тикет — это не просто уведомление. Это сигнал Чаку Норрису. Давайте закроем его, пока он не пришёл!",
            "Срочные тикеты боятся Чака Норриса, и они должны бояться тебя тоже. Время действовать!",
        ];

        $ticketsByPerformer = $tickets->groupBy('performer.name');

        foreach ($ticketsByPerformer as $performerName => $performerTickets) {
            $message = "🔥 <b>Срочно! {$performerName}</b>\n\n";
            $randomMessage = $openingMessages[array_rand($openingMessages)];
            $message .= $randomMessage . "\n\n";

            $ticketLinks = $performerTickets->map(function($ticket) {
                return $this->formatTicketLink($ticket->id);
            })->implode(', ');

            $message .= $ticketLinks;

            $this->sendTelegramNotification($message);
        }
    }

    private function formatTicketLink($ticketId): string
    {
        $url = "https://tickets.metak.az/cabinet/tickets/{$ticketId}";
        return "<a href='{$url}'>#{$ticketId}</a>";
    }

    private function sendTelegramNotification($message)
    {
        try {
            Telegram::sendMessage([
                'chat_id' => config('services.telegram.chat_id'),
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка отправки сообщения в Telegram: ' . $e->getMessage());
        }
    }
}
