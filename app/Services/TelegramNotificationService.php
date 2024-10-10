<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    private string $baseUrl;
    private string $botToken;
    private string $chatId;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->chatId = config('services.telegram.chat_id');
        $this->baseUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    public function sendMessage(string $message): bool
    {
        try {
            // Логируем сообщение перед отправкой для отладки
            Log::info('Sending Telegram message:', ['message' => $message]);

            $response = Http::get("{$this->baseUrl}/sendMessage", [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true
            ]);

            // Логируем ответ от API для отладки
            Log::info('Telegram API response:', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if (!$response->successful()) {
                Log::error('Telegram API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Telegram Notification Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    private function escapeHtml(?string $text): string
    {
        if ($text === null) {
            return '';
        }
        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5);
    }

    public function formatTicketMessage(string $action, Ticket $ticket, ?User $initiator = null): string
    {
        $baseUrl = config('app.url');
        $ticketUrl = "{$baseUrl}/cabinet/tickets/{$ticket->id}";

        return match ($action) {
            'created' => $this->formatCreatedTicketMessage($ticket, $ticketUrl),
//            'updated' => $this->formatUpdatedTicketMessage($ticket, $initiator, $ticketUrl),
//            'status_changed' => $this->formatStatusChangedMessage($ticket, $initiator, $ticketUrl),
//            'commented' => $this->formatCommentedMessage($ticket, $initiator, $ticketUrl),
//            'assigned' => $this->formatAssignedMessage($ticket, $initiator, $ticketUrl),
//            'closed' => $this->formatClosedMessage($ticket, $initiator, $ticketUrl),
//            default => $this->formatDefaultMessage($ticket, $action, $ticketUrl),
        };
    }

    private function formatCreatedTicketMessage(Ticket $ticket, string $ticketUrl): string
    {
        $text = htmlspecialchars($ticket->text ?? '', ENT_QUOTES | ENT_HTML5);
        $creatorName = htmlspecialchars($ticket->creator?->name ?? 'Неизвестный пользователь', ENT_QUOTES | ENT_HTML5);
        $performerName = htmlspecialchars($ticket->performer?->name ?? '🤷‍', ENT_QUOTES | ENT_HTML5);

        return "🔔 <b>Новый тикет #{$ticket->id}</b>\n\n" .
            "автор: <b>{$creatorName}</b>\n" .
            "исполнитель: <b>{$performerName}</b>\n\n" .
            ($text ? "\"{$text}\"\n\n" : "\n") .
            "👉 <a href='$ticketUrl'>Перейти к тикету</a>\n";
    }

//    private function formatUpdatedTicketMessage(Ticket $ticket, ?User $initiator, string $ticketUrl): string
//    {
//        $title = $this->escapeHtml($ticket->title);
//        $text = $this->escapeHtml($ticket->text);
//        $initiatorName = $this->escapeHtml($initiator?->name ?? 'Система');
//
//        return "📝 <b>Тикет #{$ticket->id} обновлен</b>\n\n" .
//            "Обновил: {$initiatorName}\n" .
//            "Заголовок: {$title}\n" .
//            ($text ? "Новый текст: \"{$text}\"\n\n" : "\n") .
//            "<a href=\"{$ticketUrl}\">Посмотреть изменения</a>";
//    }
//
//    private function formatStatusChangedMessage(Ticket $ticket, ?User $initiator, string $ticketUrl): string
//    {
//        $title = $this->escapeHtml($ticket->title);
//        $status = $this->escapeHtml($ticket->status ?? 'Не указан');
//        $initiatorName = $this->escapeHtml($initiator?->name ?? 'Система');
//
//        return "🔄 <b>Изменение статуса тикета #{$ticket->id}</b>\n\n" .
//            "Изменил: {$initiatorName}\n" .
//            "Новый статус: {$status}\n" .
//            "Заголовок: {$title}\n\n" .
//            "<a href=\"{$ticketUrl}\">Подробнее</a>";
//    }
//
//    private function formatCommentedMessage(Ticket $ticket, ?User $initiator, string $ticketUrl): string
//    {
//        $title = $this->escapeHtml($ticket->title);
//        $initiatorName = $this->escapeHtml($initiator?->name ?? 'Система');
//
//        return "💬 <b>Новый комментарий к тикету #{$ticket->id}</b>\n\n" .
//            "Автор: {$initiatorName}\n" .
//            "Заголовок тикета: {$title}\n\n" .
//            "<a href=\"{$ticketUrl}\">Прочитать комментарий</a>";
//    }
//
//    private function formatAssignedMessage(Ticket $ticket, ?User $initiator, string $ticketUrl): string
//    {
//        $title = $this->escapeHtml($ticket->title);
//        $initiatorName = $this->escapeHtml($initiator?->name ?? 'Система');
//        $assigneeName = $this->escapeHtml($ticket->assignee?->name ?? 'Не назначен');
//
//        return "👤 <b>Тикет #{$ticket->id} назначен</b>\n\n" .
//            "Назначил: {$initiatorName}\n" .
//            "Исполнитель: {$assigneeName}\n" .
//            "Заголовок: {$title}\n\n" .
//            "<a href=\"{$ticketUrl}\">Подробнее</a>";
//    }
//
//    private function formatClosedMessage(Ticket $ticket, ?User $initiator, string $ticketUrl): string
//    {
//        $title = $this->escapeHtml($ticket->title);
//        $initiatorName = $this->escapeHtml($initiator?->name ?? 'Система');
//
//        return "✅ <b>Тикет #{$ticket->id} закрыт</b>\n\n" .
//            "Закрыл: {$initiatorName}\n" .
//            "Заголовок: {$title}\n\n" .
//            "<a href=\"{$ticketUrl}\">Подробнее</a>";
//    }
//
//    private function formatDefaultMessage(Ticket $ticket, string $action, string $ticketUrl): string
//    {
//        $title = $this->escapeHtml($ticket->title);
//        $actionText = $this->escapeHtml($action);
//
//        return "🎫 <b>Действие с тикетом #{$ticket->id}</b>\n\n" .
//            "Действие: {$actionText}\n" .
//            "Заголовок: {$title}\n\n" .
//            "<a href=\"{$ticketUrl}\">Подробнее</a>";
//    }
}
