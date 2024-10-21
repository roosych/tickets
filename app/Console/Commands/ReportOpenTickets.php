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
    protected int $maxMessageLength = 4096; // Максимальная длина сообщения в Telegram

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

    private function prepareMessages($ticketsByPerformer): array
    {
        $messages = [];
        $openingMessages = [
            "Чак Норрис видит невыполненные задачи даже с закрытыми глазами. Может, пора их закончить?",
            "Даже Чак Норрис не смог бы игнорировать эти задачи. Время действовать!",
            "Когда Чак Норрис видит тикет, он решает его моментально. Ты можешь хотя бы попробовать!",
            "Чак Норрис делает работу быстрее, чем ты читаешь это сообщение. Время разбираться с тикетами!",
            "Задачи боятся Чака Норриса и исчезают сами. Но тебе придётся закрыть их вручную!",
            "В отделе открыты тикеты? Чак Норрис сказал бы, что это вызов! Пора доказать, что мы не отстаем!",
            "Каждый открытый тикет — это вызов для всего отдела. Чак Норрис знает, что мы можем его принять!",
            "Чак Норрис не позволяет открытым тикетам доживать до следующего дня. Пора действовать, команда!",
        ];

        $currentMessage = $openingMessages[array_rand($openingMessages)];
        $currentMessage .= "\n\n ⏳ <b>Открытые тикеты:</b>\n\n";

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

    private function addToMessageOrCreateNew($currentMessage, $newSection, &$messages): string
    {
        if (mb_strlen($currentMessage . $newSection) > $this->maxMessageLength) {
            $messages[] = $currentMessage;
            return $newSection;
        }
        return $currentMessage . $newSection;
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
