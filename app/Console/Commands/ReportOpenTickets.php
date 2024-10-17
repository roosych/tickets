<?php

namespace App\Console\Commands;

use App\Enums\TicketStatusEnum;
use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class ReportOpenTickets extends Command
{
    protected $signature = 'tickets:report-open';
    protected $description = 'Report all open tickets grouped by performers';

    public function handle()
    {
        $openTickets = Ticket::where('status', TicketStatusEnum::OPENED)
            ->with('performer')
            ->get();

        if ($openTickets->isEmpty()) {
            $this->info('Нет открытых тикетов.');
            return;
        }

        $ticketsByPerformer = $openTickets->groupBy('performer.name');

        $message = "Хватит ждать идеального момента — он уже настал! Вперёд за работу, товарищи! 🚀\n\n ⏳ <b>Открытые тикеты:</b>\n\n";

        // Сначала обрабатываем тикеты без исполнителя
        if ($ticketsByPerformer->has('')) {
            $unassignedTickets = $ticketsByPerformer['']->pluck('id')->map(function($id) {
                return "#" . $id;
            })->implode(', ');
            $message .= "Без исполнителя - {$unassignedTickets}\n\n";
            $ticketsByPerformer->forget('');
        }

        // Затем обрабатываем остальные тикеты
        foreach ($ticketsByPerformer as $performerName => $tickets) {
            $ticketNumbers = $tickets->pluck('id')->map(function($id) {
                return "#" . $id;
            })->implode(', ');

            $message .= "<b>{$performerName}</b> \n {$ticketNumbers}\n\n";
        }

        $this->sendTelegramNotification($message);
        $this->info('Отчет по открытым тикетам отправлен в Telegram.');
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
