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
    protected $maxMessageLength = 4096; // Максимальная длина сообщения в Telegram

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

        $messages = $this->prepareMessages($ticketsByPerformer);

        foreach ($messages as $message) {
            $this->sendTelegramNotification($message);
        }

        $this->info('Отчет по открытым тикетам отправлен в Telegram.');
    }

    private function prepareMessages($ticketsByPerformer)
    {
        $messages = [];
        $currentMessage = "Хватит ждать идеального момента — он уже настал! Вперёд за работу, товарищи! 🚀\n\n ⏳ <b>Открытые тикеты:</b>\n\n";

        // Сначала обрабатываем тикеты без исполнителя
        if ($ticketsByPerformer->has('')) {
            $unassignedTickets = $ticketsByPerformer['']->map(function($ticket) {
                return $this->formatTicketLink($ticket->id);
            })->implode(', ');
            $unassignedSection = "Без исполнителя - {$unassignedTickets}\n\n";
            $currentMessage = $this->addToMessageOrCreateNew($currentMessage, $unassignedSection, $messages);
            $ticketsByPerformer->forget('');
        }

        // Затем обрабатываем остальные тикеты
        foreach ($ticketsByPerformer as $performerName => $tickets) {
            $ticketLinks = $tickets->map(function($ticket) {
                return $this->formatTicketLink($ticket->id);
            })->implode(', ');

            $performerSection = "<b>{$performerName}</b> \n {$ticketLinks}\n\n";
            $currentMessage = $this->addToMessageOrCreateNew($currentMessage, $performerSection, $messages);
        }

        if (!empty($currentMessage)) {
            $messages[] = $currentMessage;
        }

        return $messages;
    }

    private function addToMessageOrCreateNew($currentMessage, $newSection, &$messages)
    {
        if (mb_strlen($currentMessage . $newSection) > $this->maxMessageLength) {
            $messages[] = $currentMessage;
            return $newSection;
        }
        return $currentMessage . $newSection;
    }

    private function formatTicketLink($ticketId)
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
